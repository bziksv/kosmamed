import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { formatFieldsWithConfig } from 'im.v2.model';

import { builderFieldsConfig } from './field-config';

import type { JsonObject } from 'main.core';
import type { Store, ActionTree, GetterTree, MutationTree } from 'ui.vue3.vuex';

type RawBuilderMessage = {
	id: number,
	builder: {
		blocks: Block[]
	},
};

type BlocksBuilderParams = {
	// here can be some params for builder, for example, background color, side line color etc.
};

type RawBlocksBuilder = {
	messageId: number,
	blocks: Block,
};

type Block = {
	type: string,
	text: string,
};

type BuilderState = {
	builderCollection: Map<number, BlocksBuilderParams>,
	blockCollection: Map<number, Block[]>,
};

/* eslint-disable no-param-reassign */
export class MessageBuilderModel extends BuilderModel
{
	getName(): string
	{
		return 'messageBuilder';
	}

	getState(): BuilderState
	{
		return {
			builderCollection: new Map(),
			blockCollection: new Map(),
		};
	}

	getBuilderElementState(): BlocksBuilderParams
	{
		return {};
	}

	getBlockElementState(): Block
	{
		return {
			type: '',
			text: '',
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function messages/builder/hasBlocks */
			hasBlocks: (state: BuilderState) => (messageId: number): boolean => {
				return Type.isArrayFilled(state.blockCollection.get(messageId));
			},
			/** @function messages/builder/getBlocks */
			getBlocks: (state: BuilderState) => (messageId: number): Block[] => {
				return state.blockCollection.get(messageId) ?? [];
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function messages/builder/set */
			set: (store: Store, rawMessages: RawBuilderMessage | RawBuilderMessage[]) => {
				const messages = Type.isArray(rawMessages) ? rawMessages : [rawMessages];
				const builderMessages = messages.filter((message) => message.builder);

				builderMessages.forEach((builderMessage) => {
					const { id: messageId, builder } = builderMessage;
					const preparedBuilder = this.#formatFields(builder);
					const { blocks } = preparedBuilder;
					delete preparedBuilder.blocks;

					store.commit('addBuilder', {
						messageId,
						params: { ...this.getBuilderElementState(), ...preparedBuilder },
					});

					const preparedBlocks = blocks.map((block) => {
						return { ...this.getBlockElementState(), ...block };
					});

					store.commit('addBlocks', { messageId, blocks: preparedBlocks });
				});
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			addBuilder: (state: BuilderState, payload: { messageId: number, params: BlocksBuilderParams }) => {
				const { messageId, params } = payload;
				state.builderCollection.set(messageId, params);
			},
			addBlocks: (state: BuilderState, payload: { messageId: number, blocks: Block[] }) => {
				const { messageId, blocks } = payload;
				state.blockCollection.set(messageId, blocks);
			},
		};
	}

	#formatFields(item: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(item, builderFieldsConfig);
	}
}
