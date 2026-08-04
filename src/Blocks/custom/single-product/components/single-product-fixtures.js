// Editor-preview fixture data — mirrors the flavor shape that
// single-product.php seeds via wp_interactivity_state(), so the preview
// markup stays interchangeable with the frontend template. Ported from
// _mockups/single-product/app.js (beverage line); brand colors mirror the
// theme's --wp--preset--color--package-N pairs.

import kiwiStrawberry from '../assets/beverage-kiwi-strawberry.webp';
import hibiscusSpearmint from '../assets/beverage-hibiscus-spearmint.webp';
import jasmineLemon from '../assets/beverage-jasmine-lemon.webp';
import bloodOrangeLime from '../assets/beverage-blood-orange-lime.webp';
import blackberryLemon from '../assets/beverage-blackberry-lemon.webp';

const packOptions = [
	{ label: 'Single can', quantity: 1, price: 7, priceHtml: '$7.00' },
	{ label: '4 pack of 12 oz cans', quantity: 4, price: 25, priceHtml: '$25.00' },
];

export const fixtureFlavors = [
	{
		id: 1,
		name: 'Kiwi Strawberry',
		image: kiwiStrawberry,
		// package-1
		cardBg: '#FBC8B4',
		nameColor: '#007F7E',
		pageBg: '#FBC8B4',
		pageContrast: '#007F7E',
		description: 'Any Time Beverage\n\nLive in the moment ANY TIME with our premium hemp-derived 5mg THC and 2.5mg CBG per serving beverage.\n\nEnjoy a 4-pack of our kiwi-strawberry flavored drink today! Naturally sweetened with monk fruit and always vegan, dairy- and gluten-free!',
		cannafacts: 'Per serving: 5mg THC, 2.5mg CBG\nServing size: 12 fl oz\nServings per container: 4',
		ingredients: '10mg of Hemp Derived THC, Sparkling Water, Natural Kiwi & Strawberry Flavoring, Monk Fruit & CBG',
		starsAvg: 3.5,
		reviewCount: 10,
		mood: 'Anytime',
		packOptions,
	},
	{
		id: 2,
		name: 'Hibiscus Spearmint',
		image: hibiscusSpearmint,
		// package-10
		cardBg: '#B8E5E7',
		nameColor: '#CF3647',
		pageBg: '#B8E5E7',
		pageContrast: '#CF3647',
		description: 'Hibiscus Spearmint\n\nCrisp, floral, and refreshing.',
		cannafacts: 'Per serving: 5mg THC, 2.5mg CBG\nServing size: 12 fl oz',
		ingredients: 'Sparkling water, hibiscus extract, spearmint extract, monk fruit, CBG.',
		starsAvg: 4.0,
		reviewCount: 8,
		mood: 'Daytime',
		packOptions,
	},
	{
		id: 3,
		name: 'Jasmine Lemon',
		image: jasmineLemon,
		// package-9
		cardBg: '#F0F0C0',
		nameColor: '#13775F',
		pageBg: '#F0F0C0',
		pageContrast: '#13775F',
		description: 'Jasmine Lemon\n\nBright citrus with a floral finish.',
		cannafacts: 'Per serving: 5mg THC, 2.5mg CBG\nServing size: 12 fl oz',
		ingredients: 'Sparkling water, jasmine tea, lemon juice, monk fruit, CBG.',
		starsAvg: 4.5,
		reviewCount: 12,
		mood: 'Daytime',
		packOptions,
	},
	{
		id: 4,
		name: 'Blood Orange Lime',
		image: bloodOrangeLime,
		// package-11
		cardBg: '#F37021',
		nameColor: '#D3E5AD',
		pageBg: '#F37021',
		pageContrast: '#D3E5AD',
		description: 'Blood Orange Lime\n\nBright citrus with a juicy snap.',
		cannafacts: 'Per serving: 5mg THC, 2.5mg CBG\nServing size: 12 fl oz',
		ingredients: 'Sparkling water, blood orange juice, lime, monk fruit, CBG.',
		starsAvg: 4.3,
		reviewCount: 9,
		mood: 'Anytime',
		packOptions,
	},
	{
		id: 5,
		name: 'Blackberry Lemon',
		image: blackberryLemon,
		// package-3
		cardBg: '#8F53A1',
		nameColor: '#FFF450',
		pageBg: '#8F53A1',
		pageContrast: '#FFF450',
		description: 'Blackberry Lemon\n\nBold, dark berry with a citrus kick.',
		cannafacts: 'Per serving: 5mg THC, 2.5mg CBG\nServing size: 12 fl oz',
		ingredients: 'Sparkling water, blackberry, lemon, monk fruit, CBG.',
		starsAvg: 4.4,
		reviewCount: 11,
		mood: 'Nighttime',
		packOptions,
	},
];
