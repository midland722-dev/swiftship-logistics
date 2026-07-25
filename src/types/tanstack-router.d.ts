declare module '@tanstack/react-router' {
  import { Router } from '@tanstack/router-core';
  
  interface Router {
    (props: Record<string, unknown>): import('react').ReactNode;
  }
}
