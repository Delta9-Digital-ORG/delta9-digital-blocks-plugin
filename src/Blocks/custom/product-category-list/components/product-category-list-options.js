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

export const ProductCategoryListOptions = ({ attributes, setAttributes }) => {
	
	const productCategoryListName = checkAttr('productCategoryListName', attributes, manifest);
	const productCategoryListFormat = checkAttr('productCategoryListFormat', attributes, manifest);
	
	return (
		<>
			<Section icon={icons.tools} label={__('Category Name', 'delta9-digital-blocks-plugin')} >
				<Select
		        	label={__('Category Name', 'delta9-digital-blocks-plugin')}
		        	value={productCategoryListName}
		        	options={manifest.allowed.categories}
					onChange={(value) => setAttributes({ [getAttrKey('productCategoryListName', attributes, manifest)]: value })}
		        />
			</Section>
	        
			<Section icon={icons.tools} label={__('Category Display Format', 'delta9-digital-blocks-plugin')} >
				<Select
		        	label={__('Category Display Format', 'delta9-digital-blocks-plugin')}
		        	value={productCategoryListFormat}
		        	options={manifest.allowed.displayFormat}
					onChange={(value) => setAttributes({ [getAttrKey('productCategoryListFormat', attributes, manifest)]: value })}
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