import type { Page } from '@playwright/test';
/**
 * Obtiene el token CSRF
 */
export declare function getCsrfToken(page: Page): Promise<string>;
/**
 * Crea modelos usando factory
 */
export declare function factory<T = any>(page: Page, options: {
    model: string;
    count?: number;
    relationships?: Array<any>;
    attributes?: Record<string, any>;
    states?: Array<string>;
    load?: Array<string>;
}): Promise<T>;
/**
 * Inicia sesión con un usuario
 */
export declare function login<T = any>(page: Page, options?: {
    id?: number;
    attributes?: Record<string, any>;
    relationships?: Array<any>;
    states?: Array<string>;
    load?: Array<string>;
}): Promise<T>;
/**
 * Cierra la sesión actual
 */
export declare function logout(page: Page): Promise<void>;
/**
 * Obtiene el usuario autenticado
 */
export declare function getUser<T = any>(page: Page): Promise<T>;
