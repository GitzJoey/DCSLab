export type AnyProps = Record<string, any>;

export interface SlotParams<T> {
    props: AnyProps;
    items: T[];
    isValid: (item: T) => boolean;
    getProps: (item: T) => AnyProps;
    getChildren: (item: T) => any;
}

export type SlotResult<T> =
    | {
        type: "slotted";
        target: T;
        props: AnyProps;
        children: any;
    }
    | {
        type: "wrapper";
        target: "div";
        props: AnyProps;
        children: T[];
    };

/**
 * mergeProps: Pure vanilla logic to combine attributes/props.
 * Enhanced to handle framework-agnostic class merging and deduplication.
 */
export function mergeProps(slotProps: AnyProps, childProps: AnyProps): AnyProps {
    const result: AnyProps = { ...childProps };

    for (const key in slotProps) {
        const slotValue = slotProps[key];

        // 1. Event Handlers (Merge them to execute both)
        const isHandler = /^on[A-Z]/.test(key);
        if (isHandler) {
            const childValue = childProps[key];
            if (typeof slotValue === "function" && typeof childValue === "function") {
                result[key] = (...args: any[]) => {
                    childValue(...args);
                    slotValue(...args);
                };
            } else if (slotValue) {
                result[key] = slotValue;
            }
            continue;
        }

        // 2. Class Names (Handle 'class' and 'className' as synonyms)
        if (key === "class" || key === "className") {
            const slotClasses = (slotValue || "").split(/\s+/);
            const childClasses = (childProps.class || childProps.className || "").split(/\s+/);

            // Deduplicate classes
            const combined = Array.from(new Set([...slotClasses, ...childClasses]))
                .filter(Boolean)
                .join(" ");

            // Update the key that was provided, and sync the other if it exists
            result[key] = combined;
            const otherKey = key === "class" ? "className" : "class";
            if (otherKey in childProps) {
                result[otherKey] = combined;
            }
            continue;
        }

        // 3. Styles (Object merge)
        if (key === "style") {
            result[key] = { ...slotValue, ...childProps.style };
            continue;
        }

        // 4. Default attribute override
        if (childProps[key] === undefined) {
            result[key] = slotValue;
        }
    }

    return result;
}

/**
 * flattenItems: A generic utility to flatten hierarchies based on a marker (like Fragments).
 */
export function flattenItems<T>(
    items: T | T[],
    isFragment: (item: T) => boolean,
    getChildren: (item: T) => T | T[]
): T[] {
    const result: T[] = [];
    const list = Array.isArray(items) ? items : [items];

    list.forEach((item) => {
        if (item === null || item === undefined) return;

        if (isFragment(item)) {
            const children = getChildren(item);
            result.push(...flattenItems(children, isFragment, getChildren));
        } else {
            result.push(item);
        }
    });

    return result;
}

/**
 * calculateSlot: Decides which element gets the props.
 */
export function calculateSlot<T>(params: SlotParams<T>): SlotResult<T> {
    const { props, items, isValid, getProps, getChildren } = params;

    if (items.length === 1) {
        const primary = items[0];
        if (primary !== undefined && isValid(primary)) {
            return {
                type: "slotted",
                target: primary,
                props: mergeProps(props, getProps(primary)),
                children: getChildren(primary),
            };
        }
    }

    return {
        type: "wrapper",
        target: "div",
        props: props,
        children: items,
    };
}
