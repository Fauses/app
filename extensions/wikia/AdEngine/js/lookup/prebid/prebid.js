/*global define*/
define('ext.wikia.adEngine.lookup.prebid', [
	'ext.wikia.adEngine.adContext',
	'ext.wikia.adEngine.lookup.prebid.adapters.appnexus',
	'ext.wikia.adEngine.lookup.prebid.adapters.indexExchange',
	'ext.wikia.adEngine.lookup.prebid.prebidHelper',
	'ext.wikia.adEngine.lookup.lookupFactory',
	'wikia.document',
	'wikia.window'
], function (adContext, appnexus, index, helper, factory, doc, win) {
	'use strict';

	var adapters = [
			appnexus,
			index
		],
		adUnits = [],
		biddersPerformance = {},
		priceMap = {},
		bidderKey = 'hb_bidder',
		bidKey = 'hb_pb',
		sizeKey = 'hb_size';

	function call(skin, onResponse) {
		var prebid = doc.createElement('script'),
			node = doc.getElementsByTagName('script')[0];

		adUnits = helper.setupAdUnits(adapters, skin);

		if (adUnits.length > 0) {
			win.pbjs = win.pbjs || {};
			win.pbjs.que = win.pbjs.que || [];

			prebid.async = true;
			prebid.type = 'text/javascript';
			prebid.src = adContext.getContext().opts.prebidBidderUrl || '//acdn.adnxs.com/prebid/prebid.js';

			node.parentNode.insertBefore(prebid, node);

			win.pbjs.que.push(function () {

				win.pbjs.addCallback('allRequestedBidsBack', function(allBids) {
					trackBiddersPerformance(allBids);
				});

				win.pbjs.addAdUnits(adUnits);

				win.pbjs.requestBids({
					bidsBackHandler: onResponse
				});
			});
		}
	}

	function trackBiddersPerformance(allBids) {
		Object.keys(allBids).forEach(function(slotName) {
			var slotBids = allBids[slotName].bids;

			slotBids.forEach(function(bid) {
				biddersPerformance[bid.bidder] = biddersPerformance[bid.bidder] || {};
				biddersPerformance[bid.bidder][slotName] = encodeParamsForTracking({
					hb_bidder: bid.bidder,
					hb_pb: (bid.getStatusCode() === 2  || !bid.pbMg) ? 'NONE' : bid.pbMg,
					hb_size: bid.getSize()
				});
			});
		});
	}

	function encodeParamsForTracking(params) {
		if (params[bidderKey] && params[sizeKey] && params[bidKey]) {
			return [params[bidderKey], params[sizeKey], params[bidKey]].join(';');
		}

		return '';
	}

	function calculatePrices() {
		var slotName,
			slots = {};

		if (win.pbjs && typeof win.pbjs.getAdserverTargeting === 'function') {
			slots = win.pbjs.getAdserverTargeting();
		}

		for (slotName in slots) {
			if (slots.hasOwnProperty(slotName) && Object.keys(slots[slotName]).length !== 0) {
				priceMap[slotName] = encodeParamsForTracking(slots[slotName]);
			}
		}
	}

	function getPrices() {
		return priceMap;
	}

	function isSlotSupported(slotName) {
		return adUnits.some(function (adUnit) {
			return adUnit.code === slotName;
		});
	}

	function getSlotParams(slotName) {
		var params;

		if (win.pbjs && typeof win.pbjs.getAdserverTargetingForAdUnitCode === 'function') {
			params = win.pbjs.getAdserverTargetingForAdUnitCode(slotName) || {};
		}

		return params || {};
	}

	return factory.create({
		logGroup: 'ext.wikia.adEngine.lookup.prebid',
		name: 'prebid',
		call: call,
		calculatePrices: calculatePrices,
		getPrices: getPrices,
		isSlotSupported: isSlotSupported,
		encodeParamsForTracking: encodeParamsForTracking,
		getSlotParams: getSlotParams
	});
});
