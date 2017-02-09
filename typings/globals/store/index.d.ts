interface Store {
    disabled?: boolean
    set(key?: string, value?: any)
    get(key?: string, defaultVal?: any)
    has(key?: string)
    remove(key?: string)
    clear()
}