/**
 * Panel link helper.
 *
 * The sitewide maintenance banner was removed: panel availability is now only
 * surfaced on the internal /status dashboard, and panel links always work.
 */
export function PanelLink({
  href,
  children,
  className = "",
}: {
  href: string;
  children: React.ReactNode;
  className?: string;
}) {
  return (
    <a href={href} className={className}>
      {children}
    </a>
  );
}
