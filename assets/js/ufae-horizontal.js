
class UfaeHorizontal extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                flipboxContainer: '.ufae_horizontal_container',
                flipboxSwiperContainer: '.ufae-swiper-container',
                flipboxWrapper: '.swiper-wrapper',
                flipboxNavPrev: '.swiper-button-prev',
                flipboxNavNext: '.swiper-button-next',
                paginationEl: '.ufae-swiper-pagination',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        return {
            $flipboxContainer: this.$element.find(selectors.flipboxContainer),
            $flipboxSwiperContainer: this.$element.find(selectors.flipboxSwiperContainer),
            $flipboxWrapper: this.$element.find(selectors.flipboxWrapper),
            $flipboxNavPrev: this.$element.find(selectors.flipboxNavPrev),
            $flipboxNavNext: this.$element.find(selectors.flipboxNavNext),
            $paginationEl: this.$element.find(selectors.paginationEl),
        };
    }

    async bindEvents() {
        const flipboxContainer = this.elements.$flipboxContainer;

        if(flipboxContainer.length === 0){
            return;
        }

        const flipboxSwiperContainer = this.elements.$flipboxSwiperContainer;
        const flipboxWrapper = this.elements.$flipboxWrapper;
        const paginationEl = this.elements.$paginationEl;
        let flipboxNavPrev = this.elements.$flipboxNavPrev;
        let flipboxNavNext = this.elements.$flipboxNavNext;
        let slidePerView = flipboxContainer.data('ufae-slideview') || 2;


        slidePerView = parseInt(slidePerView);

        const flipBoxSwiper = await this.ufaeSwiper(flipboxSwiperContainer[0], {
            slidestoshow: slidePerView,
            navigation: {
                nextEl: flipboxNavNext[0],
                prevEl: flipboxNavPrev[0],
            },
            pagination: {
                el: paginationEl[0],
                clickable: true,
            },
            breakpoints: {
                280: {
                    slidesPerView: 1
                },
                768: {
                    slidesPerView: slidePerView > 2 ? 2 : slidePerView
                },
                1024: {
                    slidesPerView: slidePerView
                }
            },
        });
    }

    async ufaeSwiper(element, config) {
        if ('undefined' === typeof Swiper) {
            const asyncSwiper = elementorFrontend.utils.swiper;
            new asyncSwiper(element, config).then((newSwiperInstance) => {
                return newSwiperInstance;
            });
        } else {
            return new Swiper(element, config);
        }
    }

}


jQuery(window).on('elementor/frontend/init', () => {

    const addHandler = ($element) => {
        elementorFrontend.elementsHandler.addHandler(UfaeHorizontal, {
            $element,
        });
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/ufae_flipbox_widget.default', addHandler);

});