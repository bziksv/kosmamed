import { Event, Tag, Text, Type } from 'main.core';
import { Popup, type PopupOptions } from 'main.popup';

const POPUP_ANGLE_HALF_WIDTH = 17;

export type HintParams = {
	text: string,
	html: string,
	popupOptions: PopupOptions,
	position: 'top',
	timeout: number,
	interactivity: boolean,
};

class Tooltip
{
	#popup: ?Popup;
	#cursorOnPopup: boolean;

	constructor(): void
	{
		this.#popup = null;
		this.#cursorOnPopup = false;
	}

	#getCenteredAngleOffset(element: HTMLElement): number | false
	{
		const elementRect = element.getBoundingClientRect();
		const elementWidth = elementRect.width || element.offsetWidth;

		return elementWidth
			? Popup.getOption('angleLeftOffset') - POPUP_ANGLE_HALF_WIDTH + elementWidth / 2
			: false
		;
	}

	#fixPopupAngle(element: HTMLElement): void
	{
		if (!this.#popup?.angle)
		{
			return;
		}

		const popupContainer = this.#popup.getPopupContainer();

		if (!popupContainer)
		{
			return;
		}

		setTimeout(() => {
			const elementRect = element.getBoundingClientRect();
			const popupRect = popupContainer.getBoundingClientRect();

			const offset = elementRect.width
				? elementRect.left + elementRect.width / 2 - popupRect.left - POPUP_ANGLE_HALF_WIDTH
				: false
			;

			this.#popup.angle.offset = offset;

			if (this.#popup.angle.position === 'bottom')
			{
				this.#popup.angle.element.style.left = '0';
				this.#popup.angle.element.style.marginLeft = offset === false ? '' : `${offset}px`;
			}
			else
			{
				this.#popup.angle.element.style.marginLeft = '0';
				this.#popup.angle.element.style.left = offset === false ? '' : `${offset}px`;
			}
		}, 0);
	}

	show(element: HTMLElement, params: HintParams): void
	{
		this.hide(false);

		const popupClassName = [
			'ui-hint-popup',
			params.interactivity ? 'ui-hint-popup-interactivity' : '',
			params.popupOptions?.className ?? '',
		]
			.filter(Boolean)
			.join(' ');

		const popupOptions: PopupOptions = {
			id: `bx-vue-hint-${Date.now()}`,
			bindElement: element,
			bindOptions: {
				position: (params.position === 'top') ? 'top' : 'bottom',
			},
			content: Tag.render`
				<span class='ui-hint-content'>${this.#getText(element, params)}</span>
			`,
			darkMode: true,
			autoHide: true,
			cacheable: false,
			focusTrap: false,
			animation: 'fading',
			angle: true,
			...(params.popupOptions ?? null),
			className: popupClassName,
		};

		popupOptions.angle = {
			offset: this.#getCenteredAngleOffset(element),
		};

		this.#popup = new Popup(popupOptions);
		this.#popup.show();
		this.#fixPopupAngle(element);

		const popupContainer = this.#popup?.getPopupContainer();

		if (params.interactivity && popupContainer)
		{
			Event.bind(popupContainer, 'mouseenter', () => {
				this.#cursorOnPopup = true;
			});
			Event.bind(popupContainer, 'mouseleave', () => {
				this.#cursorOnPopup = false;
				this.hide(true);
			});
		}
	}

	hide(isInteractive: boolean): void
	{
		if (isInteractive)
		{
			setTimeout(() => {
				if (this.#popup && this.#popup.getPopupContainer() && !(this.#cursorOnPopup))
				{
					this.#popup.close();
				}
			}, 100);
		}
		else
		{
			this.#popup?.close();
		}
	}

	#getText(element: HTMLElement, params: HintParams): string
	{
		if (Type.isStringFilled(params) && Type.isUndefined(element.dataset.hintHtml))
		{
			return Text.encode(params);
		}

		return params.html || Text.encode(params.text) || params;
	}
}

export const tooltip = new Tooltip();
