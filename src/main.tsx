import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { getRouter } from './router'

const router = getRouter()

// biome-ignore lint: React 19 render api
createRoot(document.getElementById('root')!).render(<StrictMode><router /></StrictMode>)
