import type { Page } from '@playwright/test';
type BaseRelationship = {
    name: string;
    related: string;
    count?: number;
    states?: Array<string>;
    attributes?: Record<string, unknown>;
};
type HasRelationship = BaseRelationship & {
    method: 'has';
    pivotAttributes?: never;
};
type ForRelationship = BaseRelationship & {
    method: 'for';
    pivotAttributes?: never;
};
type HasAttachedRelationship = BaseRelationship & {
    method: 'hasAttached';
    pivotAttributes?: Record<string, unknown>;
};
type Relationship = HasRelationship | ForRelationship | HasAttachedRelationship;
type CsrfTokenProps = {
    page: Page;
};
type FactoryOptions = {
    model: string;
    attributes?: Record<string, unknown>;
    states?: Array<string>;
    count?: number;
    relationships?: Array<Relationship>;
    load?: Array<string>;
};
type FactoryProps = {
    page: Page;
    options: FactoryOptions;
};
type LoginOptionsUsingId = {
    id: number;
    load?: Array<string>;
    attributes?: never;
    relationships?: never;
    states?: never;
};
type LoginOptionsUsingFactory = {
    id?: never;
    load?: Array<string>;
    attributes: Record<string, unknown>;
    relationships?: Array<Relationship>;
    states?: Array<string>;
};
type LoginOptions = LoginOptionsUsingId | LoginOptionsUsingFactory;
type LoginProps = {
    page: Page;
    options?: LoginOptions;
};
type LogoutProps = {
    page: Page;
};
type UserProps = {
    page: Page;
};
export declare function csrfToken({ page }: CsrfTokenProps): Promise<string>;
export declare function factory<T = unknown>({ page, options }: FactoryProps): Promise<T>;
export declare function login<T = unknown>({ page, options }: LoginProps): Promise<T>;
export declare function logout({ page }: LogoutProps): Promise<void>;
export declare function user<T = unknown>({ page }: UserProps): Promise<T>;
export {};
