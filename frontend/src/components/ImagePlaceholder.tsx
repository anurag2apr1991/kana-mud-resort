type ImagePlaceholderProps = {
  label?: string;
  className?: string;
};

export function ImagePlaceholder({ label = "Image coming soon", className }: ImagePlaceholderProps) {
  return (
    <div
      className={`flex h-full w-full items-center justify-center bg-gradient-to-br from-stone-200 via-stone-100 to-stone-200 text-center text-sm font-medium text-stone-500 ${className ?? ""}`}
      aria-label={label}
    >
      {label}
    </div>
  );
}
