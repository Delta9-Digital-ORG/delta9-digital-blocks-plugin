import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { ChatWidgetEditor } from './components/chat-widget-editor';
import { ChatWidgetOptions } from './components/chat-widget-options';

export const ChatWidget = (props) => {
	return (
		<>
			<InspectorControls>
				<ChatWidgetOptions {...props} />
			</InspectorControls>
			<ChatWidgetEditor {...props} />
		</>
	);
};
