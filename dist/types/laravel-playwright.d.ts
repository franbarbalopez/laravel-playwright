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
export type Relationship = (BaseRelationship & {
    method: 'has' | 'for';
    pivotAttributes?: never;
}) | (BaseRelationship & {
    method: 'hasAttached';
    pivotAttributes?: Record<string, unknown>;
});
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
export type LoginOptions = {
    id: number;
    load?: string[];
    attributes?: never;
    relationships?: never;
    states?: never;
} | {
    id?: never;
    load?: string[];
    attributes?: Record<string, unknown>;
    relationships?: Relationship[];
    states?: string[];
};
/**
 * Gets the CSRF token for subsequent requests
 * @param page - Playwright Page instance
 * @returns The CSRF token as a string
 */
export declare function csrfToken(page: Page): Promise<string>;
/**
 * Creates models using Laravel factories
 * @template T - Return type (model or collection)
 * @param page - Playwright Page instance
 * @param options - Factory options
 * @returns A promise resolving to the created model or collection
 */
export declare function factory<T = unknown>(page: Page, options: FactoryOptions): Promise<T>;
/**
 * Logs in with an existing user or creates a new one
 * @template T - Return type (typically the user model)
 * @param page - Playwright Page instance
 * @param options - Login options (optional)
 * @returns A promise resolving to the authenticated user
 */
export declare function login<T = unknown>(page: Page, options?: LoginOptions): Promise<T>;
/**
 * Logs out the currently authenticated user
 * @param page - Playwright Page instance
 * @returns A promise resolving when the logout is completed
 */
export declare function logout(page: Page): Promise<void>;
/**
 * Gets the currently authenticated user
 * @template T - Return type (typically the user model)
 * @param page - Playwright Page instance
 * @returns A promise resolving to the currently authenticated user
 */
export declare function user<T = unknown>(page: Page): Promise<T>;
