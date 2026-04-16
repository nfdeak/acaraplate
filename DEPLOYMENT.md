# Deployment & Private Package Install

`acara-app/plate-core` is a private package in `require`. Installing plate therefore needs GitHub access to `github.com/acara-app/plate-core` — either via SSH key (local dev) or a GitHub PAT (CI / deploy). Composer pulls it transparently from the path repo (sibling checkout) when present, or from the private GitHub VCS repo otherwise.

## Acara local development

```bash
cd ~/Herd
git clone git@github.com:acara-app/plate-core.git   # sibling, one-time
git clone git@github.com:acara-app/plate.git
cd plate
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

`composer install` symlinks plate-core from the sibling via the `path` repository — edits in `../plate-core/src/` are instantly live.

## Acara production deploy

### Preferred — `COMPOSER_AUTH` env var

Inject the GitHub token via your CI/CD secrets. Never commit it.

```bash
export COMPOSER_AUTH='{"github-oauth":{"github.com":"'"$GITHUB_DEPLOY_TOKEN"'"}}'
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
```

Deploy servers don't have the `../plate-core` sibling, so Composer falls through to the VCS repo and resolves via the injected token. `vendor/acara-app/plate-core` is a full git clone pinned to the commit in `composer.lock`.

### Fallback — project-local `auth.json`

```json
{
    "github-oauth": {
        "github.com": "ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
    }
}
```

Placed at `plate/auth.json` (gitignored at `.gitignore:34`). Same `composer install --no-dev` as above.

### Dev convenience — global auth (one-time per machine)

For Acara developers who hit several private repos daily:

```bash
composer config --global --auth github-oauth.github.com ghp_xxxxxxxx
```

Writes to `~/.composer/auth.json`; applies to every project on that machine.

## Token requirements

Use a **fine-grained personal access token** with **Contents: read** scope on `acara-app/plate-core`. Rotate every 90 days. When a token rotates, update the CI secret or the project-local `auth.json`; Composer picks it up on the next install.

## Community fork (no Acara credentials)

Plate is open source, but `acara-app/plate-core` is private. Community users who fork `acara-app/plate` and want to run it locally have two options:

### Option A — strip the premium dep

```bash
git clone https://github.com/acara-app/plate.git
cd plate
composer remove acara-app/plate-core --no-update
composer install
```

Plate boots with the null-object fallbacks from `app/Providers/AppServiceProvider.php:39-41` — memory recall and extraction silently no-op, the subscription gate treats every user as premium (`App\Services\Null\NullPremiumGate`). Everything else works.

### Option B — keep it and supply your own auth

If you have a GitHub token with access to `acara-app/plate-core` (Acara team members or buyers of a future premium license), set `COMPOSER_AUTH` as above and `composer install` picks it up.

## Verifying the install mode

```bash
php -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo get_class(app(App\Contracts\Ai\Memory\ManagesMemoryContext::class)) . PHP_EOL;'
```

- Premium mode (plate-core installed): `App\Services\Memory\MemoryPromptContext`
- Community mode (plate-core removed): `App\Services\Null\NullMemoryContext`

## Troubleshooting

- `composer install` fails with `Could not authenticate against github.com` → missing or expired token. Regenerate in GitHub → Settings → Developer → Personal access tokens (fine-grained), scoped to `acara-app/plate-core`, **Contents: read**.
- `composer install` fails with `The "url" supplied for the path (../plate-core) repository does not exist` → the `path` repo option auto-skips when the directory is missing, so this error usually means the VCS repo also failed to authenticate. Fix the token first.
- Community contributor hits `Could not authenticate` → they don't have Acara credentials; point them at **Option A** above.
