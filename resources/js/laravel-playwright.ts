import type { Page } from '@playwright/test';

/**
 * Obtiene el token CSRF
 */
export async function getCsrfToken(page: Page): Promise<string> {
  const response = await page.request.get('/__playwright__/csrf_token');
  return await response.json();
}

/**
 * Crea modelos usando factory
 */
export async function factory<T = any>(
  page: Page,
  options: {
    model: string;
    count?: number;
    relationships?: Array<any>;
    attributes?: Record<string, any>;
    states?: Array<string>;
    load?: Array<string>;
  }
): Promise<T> {
  const response = await page.request.post('/__playwright__/factory', {
    data: options,
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': await getCsrfToken(page)
    }
  });
  
  return await response.json() as T;
}

/**
 * Inicia sesión con un usuario
 */
export async function login<T = any>(
  page: Page,
  options?: {
    id?: number;
    attributes?: Record<string, any>;
    relationships?: Array<any>;
    states?: Array<string>;
    load?: Array<string>;
  }
): Promise<T> {
  const response = await page.request.post('/__playwright__/login', {
    data: options || {},
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': await getCsrfToken(page)
    }
  });
  
  return await response.json() as T;
}

/**
 * Cierra la sesión actual
 */
export async function logout(page: Page): Promise<void> {
  await page.request.post('/__playwright__/logout', {
    headers: {
      'X-CSRF-TOKEN': await getCsrfToken(page)
    }
  });
}

/**
 * Obtiene el usuario autenticado
 */
export async function getUser<T = any>(page: Page): Promise<T> {
  const response = await page.request.get('/__playwright__/user');
  return await response.json() as T;
}