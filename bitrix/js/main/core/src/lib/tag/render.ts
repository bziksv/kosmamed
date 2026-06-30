import Type from '../type';
import parse from './internal/parse';
import renderNode from './internal/render-node';

export default function render(
	sections: TemplateStringsArray,
	...substitutions: Array<any>
): any
{
	const html = sections
		.reduce((acc, item, index) => {
			if (index > 0)
			{
				const substitution = substitutions[index - 1];
				if (Type.isString(substitution) || Type.isNumber(substitution))
				{
					return `${acc}${substitution}${item}`;
				}

				return `${acc}{{uid${index}}}${item}`;
			}

			return acc;
		}, sections[0])
		.replaceAll(/^\s+/gm, '')
		.replaceAll(/>\n+/g, '>')
		.replaceAll(/}\n+/g, '}');

	const ast = parse(html);

	if (ast.length === 1)
	{
		const refs: any[] = [];
		const renderedNode = renderNode({
			node: ast[0],
			substitutions,
			refs,
		});

		if (Type.isArrayFilled(refs))
		{
			return Object.fromEntries([['root', renderedNode], ...refs] as any);
		}

		return renderedNode;
	}

	if (ast.length > 1)
	{
		const refs: any[] = [];
		const renderedNodes = ast.map((node: any) => {
			return renderNode({
				node,
				substitutions,
				refs,
			});
		});

		if (Type.isArrayFilled(refs))
		{
			return Object.fromEntries([['root', renderedNodes], ...refs] as any);
		}

		return renderedNodes;
	}

	return false;
}
