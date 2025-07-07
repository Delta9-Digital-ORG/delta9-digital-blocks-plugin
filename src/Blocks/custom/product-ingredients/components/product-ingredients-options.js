import React from 'react';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { TextControl } from '@wordpress/components';
import {
	icons,
	IconLabel,
	IconToggle,
	Section,
	checkAttr,
	getAttrKey,
	Select,
} from '@eightshift/frontend-libs/scripts';
import manifest from './../manifest.json';

export const ProductCategoryOptions = ({ attributes, setAttributes }) => {
	
	const productIngredientsDisplay = checkAttr('productIngredientsDisplay', attributes, manifest);
	const productIngredientsBorder = checkAttr('productIngredientsBorder', attributes, manifest);
	const productIngredientsBorderThick = checkAttr('productIngredientsBorderThick', attributes, manifest);
	const productIngredientsFormat = checkAttr('productIngredientsFormat', attributes, manifest);
	
	return (
		<>
			<Section icon={icons.tools} label={__('Ingredient Name', 'delta9-digital-blocks-plugin')} >
				<Select
		        	label={__('Ingredient Text', 'delta9-digital-blocks-plugin')}
		        	value={productIngredientsDisplay}
		        	options={manifest.allowed.categories}
					onChange={(value) => setAttributes({ [getAttrKey('productIngredientsDisplay', attributes, manifest)]: value })}
		        />
			</Section>
			
			<IconToggle
		        icon={icons.width}
		        label={__('Show Border', 'delta9-digital-blocks-plugin')}
		        checked={productIngredientsBorder}
		        onChange={(value) => setAttributes({ [getAttrKey('productIngredientsBorder', attributes, manifest)]: value })}
	        />
	        
	        {productIngredientsBorder && (
		        <IconToggle
			        icon={icons.width}
			        label={__('Show Thick Border', 'delta9-digital-blocks-plugin')}
			        checked={productIngredientsBorderThick}
			        onChange={(value) => setAttributes({ [getAttrKey('productIngredientsBorderThick', attributes, manifest)]: value })}
		        />
	        )}
	        
			<Section icon={icons.tools} label={__('Category Display Format', 'delta9-digital-blocks-plugin')} >
				<Select
		        	label={__('Category Display Format', 'delta9-digital-blocks-plugin')}
		        	value={productIngredientsFormat}
		        	options={manifest.allowed.displayFormat}
					onChange={(value) => setAttributes({ [getAttrKey('productIngredientsFormat', attributes, manifest)]: value })}
		        />
			</Section>
		</>
	);
	
	/*
	let parentCategories;
	let wpNonce = 0;
	
	const params = {
		parent: 0
	};
	
	//TODO: Set up a REST API endpoint in WordPress to return a Nonce if the user is logged in or use the WooCommerce API keys.
	//      Code examples here: https://wordpress.stackexchange.com/questions/359056/woocommerce-rest-api-ajax-auth-401-response
	
	jQuery.ajax({
		type: 'GET',
		url: '/wp-json/wc/v3/products/categories?_wpnonce=' + wpNonce,
		data: params,
		dataType: 'json'
	}).success(function (response) {
		console.log(response);
	}).always(function (response) {
		console.log(response);
	});
	*/

};