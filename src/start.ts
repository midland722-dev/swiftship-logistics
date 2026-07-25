import { createStart, createMiddleware } from "@tanstack/react-start";

import { renderErrorPage } from "./lib/error-page";
<<<<<<< HEAD
=======
import { attachSupabaseAuth } from "@/integrations/supabase/auth-attacher";
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432

const errorMiddleware = createMiddleware().server(async ({ next }) => {
  try {
    return await next();
  } catch (error) {
    if (error != null && typeof error === "object" && "statusCode" in error) {
      throw error;
    }
    console.error(error);
    return new Response(renderErrorPage(), {
      status: 500,
      headers: { "content-type": "text/html; charset=utf-8" },
    });
  }
});

export const startInstance = createStart(() => ({
<<<<<<< HEAD
=======
  functionMiddleware: [attachSupabaseAuth],
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
  requestMiddleware: [errorMiddleware],
}));
