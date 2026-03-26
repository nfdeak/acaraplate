@props(['url' => url('/'), 'currentUrl' => url()->current()])
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [{
        "@@type": "ListItem",
        "position": 1,
        "name": "Home",
        "item": "{{ $url }}"
    },{
        "@@type": "ListItem",
        "position": 2,
        "name": "Tools",
        "item": "{{ $url }}/tools"
    },{
        "@@type": "ListItem",
        "position": 3,
        "name": "Health Sync",
        "item": "{{ $url }}/tools/health-sync"
    },{
        "@@type": "ListItem",
        "position": 4,
        "name": "Setup Guide",
        "item": "{{ $currentUrl }}"
    }]
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "HowTo",
    "name": "Set Up Acara Health Sync",
    "description": "Connect your iPhone health data to Acara Plate in 5 simple steps.",
    "totalTime": "PT5M",
    "step": [
        {
            "@@type": "HowToStep",
            "position": 1,
            "name": "Generate a Pairing Token",
            "text": "Open your Plate instance, go to Settings > Mobile Sync, and click Generate Pairing Token. You'll receive an 8-character code valid for 24 hours."
        },
        {
            "@@type": "HowToStep",
            "position": 2,
            "name": "Get the App",
            "text": "Download Acara Health Sync from the App Store on your iPhone running iOS 18.0 or later."
        },
        {
            "@@type": "HowToStep",
            "position": 3,
            "name": "Connect Your Account",
            "text": "Open Health Sync on your iPhone, scan the QR code from your Mobile Sync page or manually enter your Plate URL and pairing token, then tap Connect."
        },
        {
            "@@type": "HowToStep",
            "position": 4,
            "name": "Pick Your Data",
            "text": "Choose which health categories to share with Plate. Toggle individual data types on or off, then approve the Apple Health permissions prompt."
        },
        {
            "@@type": "HowToStep",
            "position": 5,
            "name": "Start Syncing",
            "text": "Your dashboard shows the connection status. Data syncs automatically when you open the app, or tap Sync Now for an immediate sync."
        }
    ]
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebPage",
    "name": "Setup Guide — Acara Health Sync",
    "description": "Step-by-step guide to set up Acara Health Sync. Generate a pairing token, install the iOS app, and start syncing your Apple Health data securely.",
    "speakable": {
        "@@type": "SpeakableSpecification",
        "cssSelector": [".speakable-intro"]
    },
    "url": "{{ $currentUrl }}"
}
</script>
