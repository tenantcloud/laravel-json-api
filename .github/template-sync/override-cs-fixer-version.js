const { readFile, writeFile } = require('fs/promises');

module.exports = () => async (template, source) => {
	const sourceComposerJson = JSON.parse(
		(await readFile(source.path('composer.json'))).toString()
	);

	await writeFile(
		source.path('composer.json'),
		JSON.stringify({
			...sourceComposerJson,
			'require-dev': {
				...sourceComposerJson['require-dev'],
				'php-cs-fixer/shim': '3.19.1',
			},
		})
	);

	return {
		reserved: [],
	};
};
