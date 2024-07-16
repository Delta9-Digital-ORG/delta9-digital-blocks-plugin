import MicroModal from 'micromodal';
import { Cookies } from './cookies';

export class AgeVerification {
	constructor(options) {
		this.openClass = options.openClass;
		this.componentClass = options.componentClass;
		this.jsToggleClass = options.jsToggleClass;
		this.cookieName = options.cookieName;
	}

	init() {
		MicroModal.init({
			openClass: this.openClass,
			openTrigger: `data-${this.jsToggleClass}-open`,
			closeTrigger: `data-${this.jsToggleClass}-confirm`,
			disableScroll: true,
		});
		
		const thisObj = this;
		
		$('.js-' + this.componentClass).each(function () {
			let MicroModalID = $(this)[0].id;
			
			MicroModal.show(MicroModalID);
			
			let confirmButton = $('#' + MicroModalID)
				.children('.' + thisObj.componentClass + '__overlay')
				.children('.' + thisObj.componentClass + '__dialog')
				.children('.' + thisObj.componentClass + '__close')
				.children('.' + thisObj.componentClass + '__confirm-button');
			
			// Set the on click for the confirm button
			$(confirmButton).on('click', function() {
				MicroModal.close(MicroModalID);
				
				let cookieTime = $(this).data('age-verification-time');
				
				// Set the cookie;
				let cookies = new Cookies();
				cookies.setCookie(thisObj.cookieName, true, cookieTime);
			});
			
			let declineButton = $('#' + MicroModalID)
				.children('.' + thisObj.componentClass + '__overlay')
				.children('.' + thisObj.componentClass + '__dialog')
				.children('.' + thisObj.componentClass + '__close')
				.children('.' + thisObj.componentClass + '__decline-button');
			
			// Set the on click for the redirect button
			$(declineButton).on('click', function() {
				MicroModal.close(MicroModalID);
				
				// Redirect
				window.location.href = window.location.protocol + '//' + window.location.host + '/' + $(this).data('decline-link');
			});
		});
	}
}