import { useCallback, useEffect, useState } from "react";
import { X, ChevronLeft, ChevronRight, Expand } from "lucide-react";

export type GalleryImage = {
  src: string;
  alt: string;
  caption?: string;
};

type Props = {
  eyebrow?: string;
  title: string;
  description?: string;
  images: GalleryImage[];
};

export function ImageGallery({ eyebrow, title, description, images }: Props) {
  const [openIndex, setOpenIndex] = useState<number | null>(null);
  const isOpen = openIndex !== null;

  const close = useCallback(() => setOpenIndex(null), []);
  const step = useCallback(
    (dir: number) =>
      setOpenIndex((i) => (i === null ? i : (i + dir + images.length) % images.length)),
    [images.length],
  );

  useEffect(() => {
    if (!isOpen) return;
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "Escape") close();
      if (e.key === "ArrowRight") step(1);
      if (e.key === "ArrowLeft") step(-1);
    };
    window.addEventListener("keydown", onKey);
    const prev = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      window.removeEventListener("keydown", onKey);
      document.body.style.overflow = prev;
    };
  }, [isOpen, close, step]);

  const active = openIndex === null ? null : images[openIndex];

  return (
    <section className="container-x py-16 md:py-20">
      {eyebrow && (
        <p className="font-mono text-xs font-bold uppercase tracking-widest text-accent">
          {eyebrow}
        </p>
      )}
      <h2 className="mt-2 font-display text-3xl font-bold md:text-4xl">{title}</h2>
      {description && (
        <p className="mt-3 max-w-2xl text-muted-foreground">{description}</p>
      )}

      <div className="mt-8 grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4">
        {images.map((img, i) => (
          <button
            key={img.src + i}
            type="button"
            onClick={() => setOpenIndex(i)}
            aria-label={`Open image: ${img.alt}`}
            className="group relative aspect-4/3 overflow-hidden rounded-sm border border-border focus:outline-none focus-visible:ring-2 focus-visible:ring-accent"
          >
            <img
              src={img.src}
              alt={img.alt}
              loading="lazy"
              decoding="async"
              sizes="(min-width: 1024px) 25vw, (min-width: 768px) 33vw, 50vw"
              className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
            <span className="pointer-events-none absolute inset-0 bg-foreground/0 transition-colors group-hover:bg-foreground/30" />
            <span className="pointer-events-none absolute right-2 top-2 grid h-8 w-8 place-items-center rounded-sm bg-brand text-brand-foreground opacity-0 transition-opacity group-hover:opacity-100">
              <Expand className="h-4 w-4" />
            </span>
            {img.caption && (
              <span className="pointer-events-none absolute inset-x-0 bottom-0 bg-linear-to-t from-foreground/80 to-transparent p-3 text-left text-xs font-semibold text-background opacity-0 transition-opacity group-hover:opacity-100">
                {img.caption}
              </span>
            )}
          </button>
        ))}
      </div>

      {isOpen && active && (
        <div
          role="dialog"
          aria-modal="true"
          aria-label={active.alt}
          onClick={close}
          className="fixed inset-0 z-100 flex items-center justify-center bg-foreground/90 p-4 backdrop-blur-sm"
        >
          <button
            type="button"
            onClick={close}
            aria-label="Close gallery"
            className="absolute right-4 top-4 grid h-11 w-11 place-items-center rounded-sm bg-background text-foreground hover:bg-accent hover:text-accent-foreground"
          >
            <X className="h-5 w-5" />
          </button>

          <button
            type="button"
            onClick={(e) => {
              e.stopPropagation();
              step(-1);
            }}
            aria-label="Previous image"
            className="absolute left-3 grid h-11 w-11 place-items-center rounded-sm bg-background/90 text-foreground hover:bg-accent hover:text-accent-foreground md:left-8"
          >
            <ChevronLeft className="h-5 w-5" />
          </button>

          <figure
            onClick={(e) => e.stopPropagation()}
            className="max-h-full w-full max-w-4xl"
          >
            <img
              src={active.src}
              alt={active.alt}
              decoding="async"
              className="max-h-[75vh] w-full rounded-sm object-contain"
            />
            <figcaption className="mt-4 text-center text-sm text-background/90">
              {active.caption ?? active.alt}
              <span className="ml-2 font-mono text-xs text-background/60">
                {(openIndex ?? 0) + 1} / {images.length}
              </span>
            </figcaption>
          </figure>

          <button
            type="button"
            onClick={(e) => {
              e.stopPropagation();
              step(1);
            }}
            aria-label="Next image"
            className="absolute right-3 grid h-11 w-11 place-items-center rounded-sm bg-background/90 text-foreground hover:bg-accent hover:text-accent-foreground md:right-8"
          >
            <ChevronRight className="h-5 w-5" />
          </button>
        </div>
      )}
    </section>
  );
}
