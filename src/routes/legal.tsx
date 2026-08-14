import { createFileRoute, Link } from "@tanstack/react-router";

export const Route = createFileRoute("/legal")({
  head: () => ({
    meta: [
      { title: "Legal notice — American Shipping & Logistics" },
      { name: "description", content: "Corporate information, registration details, and legal notices for American Shipping & Logistics." },
      { property: "og:title", content: "Legal notice — American Shipping & Logistics" },
      { property: "og:url", content: "/legal" },
    ],
    links: [{ rel: "canonical", href: "/legal" }],
  }),
  component: LegalPage,
});

function LegalPage() {
  return (
    <section className="container-x py-16 md:py-20">
      <p className="font-mono text-xs uppercase tracking-widest text-brand">Legal</p>
      <h1 className="mt-2 font-display text-4xl font-bold md:text-5xl">Legal notice</h1>

      <div className="prose prose-neutral mt-8 max-w-3xl text-muted-foreground">
        <h2 className="mt-8 font-display text-xl font-semibold text-foreground">Company information</h2>
        <p>
          American Shipping & Logistics AG · Heidenkampsweg 100 · 20097 Hamburg, Germany.
          Registered at the Hamburg local court, HRB 000000.
        </p>

        <h2 className="mt-8 font-display text-xl font-semibold text-foreground">Board & representation</h2>
        <p>
          Represented by the Board of Management. Chair of the Supervisory Board:
          Dr. A. Meier.
        </p>

        <h2 className="mt-8 font-display text-xl font-semibold text-foreground">Contact</h2>
        <p>
          Email: <a href="mailto:legal@voltra.example" className="text-brand hover:underline">legal@voltra.example</a>
        </p>

        <h2 className="mt-8 font-display text-xl font-semibold text-foreground">Disclaimer</h2>
        <p>
          Information on this website is provided for general purposes. While we take care to
          keep content accurate and current, American Shipping & Logistics makes no warranty as to completeness or
          fitness for a particular purpose.
        </p>

        <p className="mt-10 text-sm">
          See also our <Link to="/privacy" className="text-brand hover:underline">Privacy Notice</Link> and{" "}
          <Link to="/terms" className="text-brand hover:underline">Terms of Use</Link>.
        </p>
      </div>
    </section>
  );
}
