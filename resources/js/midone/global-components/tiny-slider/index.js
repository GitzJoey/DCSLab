import { tns } from 'tiny-slider/src/tiny-slider'

const init = (el, props) => {
    el.tns = tns({
        container: el,
        slideBy: 'page',
        mouseDrag: true,
        autoplay: true,
        controls: false,
        nav: false,
        speed: 500,
        ...props.options
    })
}

const reInit = el => {
    el.tns.destroy()
    el.tns.rebuild()
}

export { init, reInit }
