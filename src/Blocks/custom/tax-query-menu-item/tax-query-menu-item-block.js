import React from "react";
import { InspectorControls, BlockControls } from "@wordpress/block-editor";
import { taxQueryMenuItemOptions } from "./components/tax-query-menu-item-options";
import { taxQueryMenuItemToolbar } from "./components/tax-query-menu-item-toolbar";
import { taxQueryMenuItemEditor } from "./components/tax-query-menu-item-editor";

export const taxQueryMenuItem = (props) => {
  return (
    <>
      <InspectorControls>
        {taxQueryMenuItemOptions(props)}
      </InspectorControls>
      <BlockControls>
        {taxQueryMenuItemToolbar(props)}
      </BlockControls>
      {taxQueryMenuItemEditor(props)}
    </>
  );
};
