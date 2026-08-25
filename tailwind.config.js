/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		'./src/**/*.{js,php,scss}',
		'./theme/omar-perfumes/**/*.{html,php,css}',
	],
	theme: {
		extend: {
			colors: {
				perfumes: {
					ink: '#111111',
					navy: '#0B1F3A',
					deep: '#071527',
					hero: '#151513',
					gold: '#C9A227',
					muted: '#6B7280',
					border: '#E5E7EB',
					soft: '#F6F6F4',
					addi: '#EAF2FF',
				},
			},
			fontFamily: {
				brand: [ '"Cormorant Garamond"', 'Georgia', 'serif' ],
				sans: [ 'Inter', 'system-ui', 'sans-serif' ],
			},
			boxShadow: {
				whatsapp: '0 6px 18px rgba(0, 0, 0, 0.28)',
			},
		},
	},
	corePlugins: {
		preflight: false,
	},
};
