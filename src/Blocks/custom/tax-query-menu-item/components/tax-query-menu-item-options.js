import React, { useEffect, useState } from "react";
import { __ } from "@wordpress/i18n";
import { PanelBody } from "@wordpress/components";
import {
  icons,
  checkAttr,
  getAttrKey,
  IconToggle,
  getOption,
  Select,
  NumberPicker,
  getFetchWpApi,
  AsyncMultiSelect,
  Section,
  unescapeHTML,
  PresetPicker,
  label,
  props,
  OptionSelector,
} from "@eightshift/frontend-libs/scripts";
import { ButtonOptions as ButtonOptionsComponent } from '../../../components/button/components/button-options';
import manifest from "../manifest.json";

export const taxQueryMenuItemOptions = ({ attributes, setAttributes }) => {

  const [taxonomyTermOptions, setTaxonomyTermOptions] = useState([]);
  const [taxonomyOptions, setTaxonomyOptions] = useState([]);

  const taxQueryMenuItemButtonAlign = checkAttr('taxQueryMenuItemButtonAlign', attributes, manifest);

  const taxQueryMenuItemPickTaxonomyManually = checkAttr(
    "taxQueryMenuItemPickTaxonomyManually",
    attributes,
    manifest
  );

  const taxQueryMenuItemTaxonomy = checkAttr(
    "taxQueryMenuItemTaxonomy",
    attributes,
    manifest
  );
  const taxQueryMenuItemManualTerms = checkAttr(
    "taxQueryMenuItemManualTerms",
    attributes,
    manifest
  );
  const taxQueryMenuItemShowCount = checkAttr(
    "taxQueryMenuItemShowCount",
    attributes,
    manifest
  );
  const taxQueryMenuItemPickTaxonomyAll = checkAttr(
    "taxQueryMenuItemPickTaxonomyAll",
    attributes,
    manifest
  );
  const taxQueryMenuItemPickTaxonomyOther = checkAttr(
    "taxQueryMenuItemPickTaxonomyOther",
    attributes,
    manifest
  );

  const getTaxonomyTermOptions = getFetchWpApi(
		taxQueryMenuItemTaxonomy?.restName,
		{
      fields: "id,name",
      processId: ({ id }) => id,
      processLabel: ({ name }) => unescapeHTML(name)
		}
	);

  const taxonomyTermOptionsArray = () => {
    return taxonomyTermOptions;
  }; 

	

  useEffect(() => { 

    getTaxonomyTermOptions().then((response) => {
      console.log(response)
      setTaxonomyTermOptions(response);
      setAttributes({
        [getAttrKey("taxQueryMenuItemPickTaxonomyManually", attributes, manifest)]: true,
      });

    });


  }, [taxQueryMenuItemTaxonomy]);



  return (
    <PanelBody title={__("Tax Query Menu", "international-student-theme")}>
       <Section
        icon={icons.filter}
        label={__("Menu Item Type", "international-student-theme")}
      >
				<PresetPicker
					manifest={manifest}
          setAttributes={setAttributes}
          excludeDefaultsFromPresets={true}
          offButton={{
						label: __('Custom Term', 'international-student-theme'),
						icon: icons.wrapperOffAlt,
						attributes: {
							taxQueryMenuItemPickTaxonomyManually: true,
              taxQueryMenuItemPickTaxonomyAll: false,
              taxQueryMenuItemPickTaxonomyOther: false,
						},
					}}
					controlOnly
				/>
      </Section>
      <Section
        icon={icons.filter}
        label={__("Item source", "international-student-theme")}
      >
        <Select
          label={__("Taxonomy", "international-student-theme")}
          value={taxQueryMenuItemTaxonomy}
          options={manifest.allowed.taxonomies}
          onChange={(value) =>
            setAttributes({
              [getAttrKey("taxQueryMenuItemTaxonomy", attributes, manifest)]:
                value,
              [getAttrKey("taxQueryMenuItemPickTaxonomyManually",attributes,manifest
                )]: false,
              [getAttrKey("taxQueryMenuItemManualTerms",attributes,manifest
                )]: [],
            })
          }
          noBottomSpacing={!taxQueryMenuItemTaxonomy}
        />
      </Section>

      <Section showIf={taxQueryMenuItemTaxonomy} noBottomSpacing>

        {taxQueryMenuItemPickTaxonomyManually && (
          <AsyncMultiSelect
            key={taxQueryMenuItemTaxonomy}
            loadOptions={taxonomyTermOptionsArray}
            value={taxQueryMenuItemManualTerms}
            onChange={(value) =>
              setAttributes({
                [getAttrKey(
                  "taxQueryMenuItemManualTerms",
                  attributes,
                  manifest
                )]: value,
              })
            }
            noBottomSpacing
          />
        )}

        <IconToggle
          icon={icons.width}
          label={__('Show Count', 'international-student-theme')}
          checked={taxQueryMenuItemShowCount}
          onChange={(value) => setAttributes({ [getAttrKey('taxQueryMenuItemShowCount', attributes, manifest)]: value })}
        />
      </Section>

       <Section
        icon={icons.filter}
        label={__("Button", "international-student-theme")}
      >
			<ButtonOptionsComponent
				{...props('button', attributes, { setAttributes })}

				additionalControls={
					<OptionSelector
						icon={icons.horizontalAlign}
						label={__('Alignment', 'international-student-theme')}
						value={taxQueryMenuItemButtonAlign}
						options={getOption('taxQueryMenuItemButtonAlign', attributes, manifest)}
						onChange={(value) => setAttributes({ [getAttrKey('taxQueryMenuItemButtonAlign', attributes, manifest)]: value })}
						iconOnly
					/>
				}
				noLabel
				noUseToggle
				noExpandButton
			/>
		</Section>
    </PanelBody>
  );
};
