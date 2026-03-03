import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';
import type { FileUIPart } from 'ai';
import {
    ChevronDown,
    Loader2,
    MessageSquare,
    Plus,
    Send,
    UtensilsCrossed,
    X,
} from 'lucide-react';
import { useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

export const AI_MODELS = {
    'gemini-3-flash-preview': 'chat.models.gemini_3_flash',
    'gemini-3.1-pro-preview': 'chat.models.gemini_3_1_pro',
    'gpt-5-mini': 'chat.models.gpt_5_mini',
    'gpt-5-nano': 'chat.models.gpt_5_nano',
} as const;

export type AIModel = keyof typeof AI_MODELS;

export const CHAT_MODES = {
    ask: {
        label: 'chat.modes.ask',
        icon: MessageSquare,
    },
    'create-meal-plan': {
        label: 'chat.modes.meal_plan',
        icon: UtensilsCrossed,
    },
} as const;

export type ChatMode = keyof typeof CHAT_MODES;

interface Props {
    onSubmit: (message: string, files?: FileUIPart[]) => void;
    onModeChange: (mode: ChatMode) => void;
    onModelChange: (model: AIModel) => void;
    className?: string;
    disabled?: boolean;
    isLoading?: boolean;
    mode: ChatMode;
    model: AIModel;
}

function readFileAsDataURL(file: File): Promise<FileUIPart> {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => {
            resolve({
                type: 'file',
                mediaType: file.type,
                url: reader.result as string,
                filename: file.name,
            });
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

export default function ChatInput({
    className,
    onSubmit,
    onModeChange,
    onModelChange,
    disabled = false,
    isLoading = false,
    mode,
    model,
}: Props) {
    const { t } = useTranslation('common');
    const [message, setMessage] = useState('');
    const [selectedFiles, setSelectedFiles] = useState<FileUIPart[]>([]);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const hasContent = message.trim() || selectedFiles.length > 0;

    const handleSubmit = () => {
        if (!hasContent) {
            return;
        }

        const text =
            message.trim() ||
            (selectedFiles.length > 0 ? 'Analyze this image' : '');
        onSubmit(text, selectedFiles.length > 0 ? selectedFiles : undefined);
        setMessage('');
        setSelectedFiles([]);
    };

    const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleSubmit();
        }
    };

    const handleFileSelect = async (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = e.target.files;
        if (!files || files.length === 0) {
            return;
        }

        const fileParts = await Promise.all(
            Array.from(files).map(readFileAsDataURL),
        );
        setSelectedFiles((prev) => [...prev, ...fileParts]);

        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    };

    const removeFile = (index: number) => {
        setSelectedFiles((prev) => prev.filter((_, i) => i !== index));
    };

    const SelectedModeIcon = CHAT_MODES[mode].icon;

    return (
        <div className="mx-auto flex w-full max-w-3xl items-end bg-background p-0.5 md:px-4 md:py-2">
            <div
                className={cn(
                    'w-full rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900',
                    className,
                )}
            >
                {selectedFiles.length > 0 && (
                    <div className="flex flex-wrap gap-2 px-2 pt-2 sm:px-3 sm:pt-3">
                        {selectedFiles.map((file, index) => (
                            <div key={index} className="group relative">
                                <img
                                    src={file.url}
                                    alt={file.filename ?? 'Selected image'}
                                    className="size-16 rounded-lg border border-gray-200 object-cover dark:border-gray-700"
                                />
                                <button
                                    type="button"
                                    onClick={() => removeFile(index)}
                                    className="absolute -top-1.5 -right-1.5 flex size-5 items-center justify-center rounded-full bg-gray-800 text-white opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <X className="size-3" />
                                </button>
                            </div>
                        ))}
                    </div>
                )}

                <div className="p-2 sm:p-3">
                    <textarea
                        value={message}
                        onChange={(e) => setMessage(e.target.value)}
                        onKeyDown={handleKeyDown}
                        placeholder={t('chat.placeholder')}
                        disabled={disabled}
                        className="w-full resize-y bg-transparent text-base text-foreground placeholder:text-muted-foreground focus:outline-none disabled:opacity-50"
                        rows={1}
                    />
                </div>
                <div className="flex items-center justify-between px-2 pb-2">
                    <div className="flex items-center gap-2">
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="secondary"
                                    size="sm"
                                    className="gap-1.5"
                                >
                                    <SelectedModeIcon className="size-4" />
                                    {mode !== 'create-meal-plan' && (
                                        <span>{t(CHAT_MODES[mode].label)}</span>
                                    )}
                                    <ChevronDown className="size-3.5 opacity-60" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" className="w-48">
                                {Object.entries(CHAT_MODES).map(
                                    ([key, { label, icon: Icon }]) => (
                                        <DropdownMenuItem
                                            key={key}
                                            onClick={() =>
                                                onModeChange(key as ChatMode)
                                            }
                                            className={cn(
                                                'gap-2',
                                                mode === key &&
                                                    'bg-accent text-accent-foreground',
                                            )}
                                        >
                                            <Icon className="size-4" />
                                            <span>{t(label)}</span>
                                        </DropdownMenuItem>
                                    ),
                                )}
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <input
                            ref={fileInputRef}
                            type="file"
                            accept="image/*"
                            multiple
                            onChange={handleFileSelect}
                            className="hidden"
                        />
                        <Button
                            variant="outline"
                            size="icon"
                            className="size-8"
                            disabled={disabled}
                            onClick={() => fileInputRef.current?.click()}
                        >
                            <Plus className="size-4" />
                        </Button>
                    </div>

                    <div className="flex items-center gap-2">
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="gap-1.5 text-muted-foreground hover:text-foreground"
                                >
                                    <span className="max-w-[80px] truncate sm:max-w-[90px]">
                                        {t(AI_MODELS[model])}
                                    </span>
                                    <ChevronDown className="size-3.5 opacity-60" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" className="w-56">
                                {Object.entries(AI_MODELS).map(
                                    ([key, label]) => (
                                        <DropdownMenuItem
                                            key={key}
                                            onClick={() =>
                                                onModelChange(key as AIModel)
                                            }
                                            className={cn(
                                                model === key &&
                                                    'bg-accent text-accent-foreground',
                                            )}
                                        >
                                            <span>{t(label)}</span>
                                        </DropdownMenuItem>
                                    ),
                                )}
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <Button
                            variant={
                                hasContent && !disabled ? 'default' : 'ghost'
                            }
                            size="icon"
                            className={`size-8 transition-all duration-200 ${
                                hasContent && !disabled
                                    ? 'bg-emerald-600 text-white hover:bg-emerald-700'
                                    : 'text-muted-foreground hover:text-foreground'
                            }`}
                            onClick={handleSubmit}
                            disabled={!hasContent || disabled}
                        >
                            {isLoading ? (
                                <Loader2 className="size-4 animate-spin" />
                            ) : (
                                <Send className="size-4" />
                            )}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    );
}
