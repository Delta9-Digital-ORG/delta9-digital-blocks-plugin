import Swiper from 'swiper';
import { Navigation, Pagination, A11y, Autoplay, EffectFade, EffectCoverflow, EffectFlip, EffectCube, EffectCards, EffectCreative } from 'swiper/modules';
import globalManifest from './../../../manifest.json';

export class CarouselSlider {
	constructor(options) {
		this.element = options.element;
		this.blockClass = options.blockClass;
		this.nextElement = options.nextElement;
		this.prevElement = options.prevElement;
		this.paginationElement = options.paginationElement;
		this.eventName = options.eventName;
	}

	init() {
		const { breakpoints } = globalManifest.globalVariables;

		const item = this.element;

		const showItems = item?.dataset?.showItems ?? -1;
		const loopMode = item?.dataset?.swiperLoop ?? false;
		const autoPlay = item?.dataset?.swiperAutoplay ?? false;
		const effectType = item?.dataset?.swiperEffect ?? 'default';
		const speed = 500;
		const spaceBetween = 0;

		let effectTypeSwiper = effectType;
		let autoPlayEffect = autoPlay;
		let freeModeEffect = false;
		let freeModeMomentumEffect = false;
		let speedEffect = speed;
		let spaceBetweenEffet = spaceBetween;


		switch (effectType) {
			case 'fade':
				effectTypeSwiper = 'fade';
				break;
			case 'coverflow':
				effectTypeSwiper = 'coverflow';
				speedEffect = 1000;
				break;
			case 'flip':
				effectTypeSwiper = 'flip';
				speedEffect = 1000;
				break;
			case 'cube':
				effectTypeSwiper = 'cube';
				speedEffect = 1000;
				break;
			case 'cards':
				effectTypeSwiper = 'cards';
				speedEffect = 1000;
				break;
			default:
				effectTypeSwiper = 'default';
		}
		
		autoPlayEffect = autoPlay?{delay: 5000, disableOnInteraction: false}:false;
		
		console.log(effectTypeSwiper);

		new Swiper(item, {
			slideClass: 'js-block-carousel-item',
			autoplay: autoPlayEffect ?? autoPlay,
			freeMode: freeModeEffect,
			freeModeMomentum: freeModeMomentumEffect,
			loop: loopMode === 'true',
			speed: speedEffect ?? speed,
			slidesPerView: showItems,
			centeredSlides: true,
			spaceBetween: spaceBetweenEffet ?? spaceBetween, 
			effect: effectTypeSwiper,
			fadeEffect: {
    			crossFade: true
    		},
			coverflowEffect: {
				depth: 100,
				modifier: 1,
				rotate: 50,
				scale: 1,
				slideShadows: false,
				stretch: 0,
    		},
    		flipEffect: {
    			limitRotation: true,
    			slideShadows: false
    		},
    		cubeEffect: {
    			shadow: true,
    			shadowOffset: 20,
    			shadowScale: 0.94,
    			slideShadows: false    			
    		},
    		cardsEffect: {
    			perSlideOffset: 8,
    			perSlideRotate: 2,
    			rotate: true,
    			slideShadows: false
    		},
			grabCursor: true,
			modules: [
				Navigation, Pagination, A11y, Autoplay, EffectFade, EffectCoverflow, EffectFlip, EffectCube, EffectCards, EffectCreative
			],
			a11y: {
				slideRole: 'figure',
			},
			keyboard: {
				enabled: true,
			},
			breakpointsInverse: true,
			threshold: 20,
			navigation: {
				nextEl: this.nextElement,
				prevEl: this.prevElement,
			},
			pagination: {
				el: this.paginationElement,
				type: 'bullets',
				clickable: true,
			},
			breakpoints: {
				[breakpoints.tablet]: {
					slidesPerView: parseInt(showItems) === -1 ? 'auto' : parseInt(showItems),
					spaceBetween: 10,
				},
			},
			on: {
				init: () => {
					window.dispatchEvent(this.eventName);
				},
			},
		});
	}
}
