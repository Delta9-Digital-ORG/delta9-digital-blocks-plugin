import React, { useMemo } from 'react';
import { __ } from "@wordpress/i18n";
import { outputCssVariables, getUnique, props } from '@eightshift/frontend-libs/scripts';
import { ButtonEditor as ButtonEditorComponent } from '../../../components/button/components/button-editor';
import { checkAttr, getAttrKey } from "@eightshift/frontend-libs/scripts";
import manifest from "../manifest.json";

import globalManifest from './../../../manifest.json';

export const taxQueryMenuItemEditor = ({ attributes, setAttributes }) => {
 const unique = useMemo(() => getUnique(), []);

	const {
		blockClass,
    	taxQueryMenuItemManualTerms,
		taxQueryMenuItemTaxonomy,
		taxQueryMenuItemPickTaxonomyAll,
		taxQueryMenuItemPickTaxonomyOther,
		taxQueryMenuItemButtonVariant
	} = attributes;
	console.log(taxQueryMenuItemManualTerms);


  const taxQueryMenuItemAll = taxQueryMenuItemPickTaxonomyAll || false;
  const taxQueryMenuItemOther = taxQueryMenuItemPickTaxonomyOther || false;
  let taxQueryMenuItemButtonContentLabel = taxQueryMenuItemManualTerms?.length > 0 ? taxQueryMenuItemManualTerms[0].label || null : null;
  const taxQueryMenuItemButtonContentSlug = taxQueryMenuItemButtonContentLabel?.replace(/([a-z])([A-Z])/g, "$1-$2").replace(/[\s_]+/g, '-').toLowerCase() || null;
  const taxQueryMenuItemButtonIconName =  `term-${taxQueryMenuItemTaxonomy?.value}-${taxQueryMenuItemButtonContentSlug}-icon` || null;

  console.log(taxQueryMenuItemButtonIconName);

 if (taxQueryMenuItemAll) {
	taxQueryMenuItemButtonContentLabel = __('View all', 'eightshift-frontend-libs');
  }

	if (taxQueryMenuItemOther) {
	taxQueryMenuItemButtonContentLabel = __('Other', 'eightshift-frontend-libs');
	}

  setAttributes({ [getAttrKey('taxQueryMenuItemButtonContent', attributes, manifest)]: taxQueryMenuItemButtonContentLabel || 'Please select a term' });
  setAttributes({ [getAttrKey('taxQueryMenuItemButtonIconName', attributes, manifest)]: taxQueryMenuItemButtonIconName });
  let blockClassVariant = `${blockClass} tax-query-menu-item__${taxQueryMenuItemButtonVariant}__btn`;

	return (
		<div className={blockClass} data-id={unique}>
			{outputCssVariables(attributes, manifest, unique, globalManifest)}

			<ButtonEditorComponent
				{...props('button', attributes, {
					setAttributes,
				})}
			/>
		</div>
	);
};
