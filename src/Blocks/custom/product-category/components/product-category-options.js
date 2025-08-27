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
	
	const productCategorySlug = checkAttr('productCategorySlug', attributes, manifest);
	const productCategoryBorder = checkAttr('productCategoryBorder', attributes, manifest);
	const productCategoryBorderThick = checkAttr('productCategoryBorderThick', attributes, manifest);
	const productCategoryDisplayGrandchildren = checkAttr('productCategoryDisplayGrandchildren', attributes, manifest);
	const productCategoryDisplayIcon = checkAttr('productCategoryDisplayIcon', attributes, manifest);
	const productCategoryFormat = checkAttr('productCategoryFormat', attributes, manifest);
	
	return (
		<>
			<Section icon={icons.tools} label={__('Category Name', 'delta9-digital-blocks-plugin')} >
				<Select
		        	label={__('Category Name', 'delta9-digital-blocks-plugin')}
		        	value={productCategorySlug}
		        	options={manifest.allowed.categories}
					onChange={(value) => setAttributes({ [getAttrKey('productCategorySlug', attributes, manifest)]: value })}
		        />
			</Section>
			
			<IconToggle
		        icon={icons.width}
		        label={__('Show Border', 'delta9-digital-blocks-plugin')}
		        checked={productCategoryBorder}
		        onChange={(value) => setAttributes({ [getAttrKey('productCategoryBorder', attributes, manifest)]: value })}
	        />
	        
	        {productCategoryBorder && (
		        <IconToggle
			        icon={icons.width}
			        label={__('Show Thick Border', 'delta9-digital-blocks-plugin')}
			        checked={productCategoryBorderThick}
			        onChange={(value) => setAttributes({ [getAttrKey('productCategoryBorderThick', attributes, manifest)]: value })}
		        />
	        )}
	        
	        <IconToggle
		        icon={icons.width}
		        label={__('Display Grandchildren', 'delta9-digital-blocks-plugin')}
		        checked={productCategoryDisplayGrandchildren}
		        onChange={(value) => setAttributes({ [getAttrKey('productCategoryDisplayGrandchildren', attributes, manifest)]: value })}
	        />
	        
	        <IconToggle
		        icon={icons.width}
		        label={__('Display Icon', 'delta9-digital-blocks-plugin')}
		        checked={productCategoryDisplayIcon}
		        onChange={(value) => setAttributes({ [getAttrKey('productCategoryDisplayIcon', attributes, manifest)]: value })}
	        />
	        
			<Section icon={icons.tools} label={__('Category Display Format', 'delta9-digital-blocks-plugin')} >
				<Select
		        	label={__('Category Display Format', 'delta9-digital-blocks-plugin')}
		        	value={productCategoryFormat}
		        	options={manifest.allowed.displayFormat}
					onChange={(value) => setAttributes({ [getAttrKey('productCategoryFormat', attributes, manifest)]: value })}
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