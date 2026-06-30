import { Type } from 'main.core';

import { formatFieldsWithConfig, isNumberOrString, type FieldsConfig } from '../../../registry';

export const builderFieldsConfig: FieldsConfig = [
	{
		fieldName: 'blocks',
		targetFieldName: 'blocks',
		checkFunction: Type.isArray,
		formatFunction: (target) => {
			return target.map((block) => formatFieldsWithConfig(block, blocksBuilderFieldsConfig));
		},
	},
];

const blocksBuilderFieldsConfig: FieldsConfig = [
	{
		fieldName: 'type',
		targetFieldName: 'type',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'text',
		targetFieldName: 'text',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'color',
		targetFieldName: 'color',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'size',
		targetFieldName: 'size',
		checkFunction: isNumberOrString,
	},
	{
		fieldName: 'imageUrl',
		targetFieldName: 'imageUrl',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'icon',
		targetFieldName: 'icon',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'status',
		targetFieldName: 'status',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'elements',
		targetFieldName: 'elements',
		checkFunction: Type.isArray,
		formatFunction: (target) => {
			return target.map((block) => formatFieldsWithConfig(block, blocksBuilderFieldsConfig));
		},
	},
	{
		fieldName: 'rows',
		targetFieldName: 'rows',
		checkFunction: Type.isArray,
		formatFunction: (row) => {
			return row.map((column) => {
				return column.map((block) => formatFieldsWithConfig(block, blocksBuilderFieldsConfig));
			});
		},
	},
];
