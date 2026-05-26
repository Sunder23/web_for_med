export const logDebug = (message, context = {}) => {
	const logLevel = window.siteVars?.logLevel ?? window.localStorage?.getItem('LOG_LEVEL');

	if (logLevel === 'debug') {
		console.debug(`[web_for_med] ${message}`, context);
	}
};
