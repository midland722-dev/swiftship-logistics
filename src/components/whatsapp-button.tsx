import { MessageCircle } from "lucide-react";

const WHATSAPP_NUMBER = "12025947566";
const WHATSAPP_MESSAGE = "Hello! I'd like to inquire about your shipping services.";
const WHATSAPP_LABEL = "Chat with us on WhatsApp";

export function WhatsAppButton() {
  const href = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(WHATSAPP_MESSAGE)}`;

  return (
    <a
      href={href}
      target="_blank"
      rel="noopener noreferrer"
      aria-label={WHATSAPP_LABEL}
      className="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg transition hover:scale-110 focus:outline-none focus:ring-2 focus:ring-[#25D366] focus:ring-offset-2"
    >
      <MessageCircle className="h-7 w-7" strokeWidth={2} />
      <span className="sr-only">{WHATSAPP_LABEL}</span>
    </a>
  );
}
