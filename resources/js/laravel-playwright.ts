import type { Page } from '@playwright/test';

/**
 * Supported relationship methods
 */
export type RelationshipMethod = 'has' | 'for' | 'hasAttached';

/**
 * Common base for all relationships
 */
export interface BaseRelationship {
  name: string;
  related: string;
  count?: number;
  states?: string[];
  attributes?: Record<string, unknown>;
  method: RelationshipMethod;
}

/**
 * Discriminated union for different relationship types
 */
export type Relationship = 
  | (BaseRelationship & { method: 'has' | 'for', pivotAttributes?: never })
  | (BaseRelationship & { method: 'hasAttached', pivotAttributes?: Record<string, unknown> });

/**
 * Options for factory
 */
export interface FactoryOptions {
  model: string;
  attributes?: Record<string, unknown>;
  states?: string[];
  count?: number;
  relationships?: Relationship[];
  load?: string[];
}

/**
 * Types for login with discrimination
 */
export type LoginOptions = 
  | { id: number | string; load?: string[]; attributes?: never; relationships?: never; states?: never }
  | { id?: never; load?: string[]; attributes?: Record<string, unknown>; relationships?: Relationship[]; states?: string[] };

/**
 * Common HTTP headers for all requests
 */
const commonHeaders = {
  Accept: 'application/json'
};

/**
 * Gets the CSRF token for subsequent requests
 * @param page - Playwright Page instance
 * @returns The CSRF token as a string
 */
export async function csrfToken(page: Page): Promise<string> {
  const response = await page.request.get('/__playwright__/csrf_token', { 
    headers: commonHeaders
  });

  return response.json();
}

/**
 * Creates models using Laravel factories
 * @template T - Return type (model or collection)
 * @param page - Playwright Page instance
 * @param options - Factory options
 * @returns A promise resolving to the created model or collection
 */
export async function factory<T = unknown>(
  page: Page, 
  options: FactoryOptions
): Promise<T> {
  const token = await csrfToken(page);

  const response = await page.request.post('/__playwright__/factory', {
    headers: { ...commonHeaders, 'X-CSRF-TOKEN': token },
    data: options,
  });
  
  return response.json() as T;
}

/**
 * Logs in with an existing user or creates a new one
 * @template T - Return type (typically the user model)
 * @param page - Playwright Page instance
 * @param options - Login options (optional)
 * @returns A promise resolving to the authenticated user
 */
export async function login<T = unknown>(
  page: Page, 
  options?: LoginOptions
): Promise<T> {
  const token = await csrfToken(page);

  const response = await page.request.post('/__playwright__/login', {
    headers: { ...commonHeaders, 'X-CSRF-TOKEN': token },
    data: options || {},
  });
  
  return response.json() as T;
}

/**
 * Logs out the currently authenticated user
 * @param page - Playwright Page instance
 * @returns A promise resolving when the logout is completed
 */
export async function logout(page: Page): Promise<void> {
  const token = await csrfToken(page);

  await page.request.post('/__playwright__/logout', {
    headers: { ...commonHeaders, 'X-CSRF-TOKEN': token },
  });
}

/**
 * Gets the currently authenticated user
 * @template T - Return type (typically the user model)
 * @param page - Playwright Page instance
 * @returns A promise resolving to the currently authenticated user
 */
export async function user<T = unknown>(page: Page): Promise<T> {
  const response = await page.request.get('/__playwright__/user', { 
    headers: commonHeaders 
  });

  return response.json() as T;
}

/**
 * Runs an Artisan command
 * @param page - Playwright Page instance
 * @param command - The Artisan command to run
 * @param parameters - Additional parameters for the command
 */
export async function artisan(page: Page, command: string, parameters: Record<string, unknown> = {}): Promise<void> {
  const token = await csrfToken(page);

  await page.request.post('/__playwright__/artisan', {
    headers: { ...commonHeaders, 'X-CSRF-TOKEN': token },
    data: { command, parameters },
  });
}

/**
 * Refreshes the database
 * @param page - Playwright Page instance
 * @param parameters - Additional parameters for the command
 */
export async function refreshDatabase(page: Page, parameters: Record<string, unknown> = {}): Promise<void> {
  return await artisan(page, 'migrate:fresh', parameters);
}

/**
 * Seeds the database
 * @param page - Playwright Page instance
 * @param seederClass - The seeder class to use
 */
export async function seedDatabase(page: Page, seederClass: string = ''): Promise<void> {
  const parameters: Record<string, string> = {};

  if (seederClass) {
    parameters['--class'] = seederClass;
  }

  return await artisan(page, 'db:seed', parameters);
}

