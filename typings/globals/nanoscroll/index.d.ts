interface nanoScrollArguments {
}
interface NanoScroll {
    isActive?: boolean
    contentScrollTop?: number
    maxScrollTop?: number
    content?: HTMLElement
    el?: HTMLElement
    reset()
    destroy()
    updateScrollValues()
    scrollTo(node?: HTMLElement)
    scrollTop(offsetY?: number)
    scrollBottom(offsetY?: number)
}
interface HTMLElement {
    nanoscroller?: NanoScroll
}
interface JQuery {
    nanoScroller(arguments?: nanoScrollArguments): JQuery
}