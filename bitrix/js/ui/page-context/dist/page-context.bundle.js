/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core_events) {
	'use strict';

	var _layers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layers");
	class ContextStack {
	  constructor() {
	    Object.defineProperty(this, _layers, {
	      writable: true,
	      value: [new Map()]
	    });
	  }
	  push() {
	    babelHelpers.classPrivateFieldLooseBase(this, _layers)[_layers].push(new Map());
	  }
	  pop() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layers)[_layers].length > 1) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layers)[_layers].pop();
	    }
	  }
	  current() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _layers)[_layers][babelHelpers.classPrivateFieldLooseBase(this, _layers)[_layers].length - 1];
	  }
	  get depth() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _layers)[_layers].length;
	  }
	}

	function getUrl() {
	  return window.location.pathname + window.location.search + window.location.hash;
	}

	const SLIDER_EVENT_OPEN = 'SidePanel.Slider:onOpenComplete';
	const SLIDER_EVENT_CLOSE = 'SidePanel.Slider:onCloseComplete';
	var _stack = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stack");
	var _collectSystem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("collectSystem");
	var _subscribeToSliderEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToSliderEvents");
	var _handleSliderOpen = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleSliderOpen");
	var _handleSliderClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleSliderClose");
	class PageContextClass {
	  constructor() {
	    Object.defineProperty(this, _subscribeToSliderEvents, {
	      value: _subscribeToSliderEvents2
	    });
	    Object.defineProperty(this, _collectSystem, {
	      value: _collectSystem2
	    });
	    Object.defineProperty(this, _stack, {
	      writable: true,
	      value: new ContextStack()
	    });
	    Object.defineProperty(this, _handleSliderOpen, {
	      writable: true,
	      value: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack].push();
	      }
	    });
	    Object.defineProperty(this, _handleSliderClose, {
	      writable: true,
	      value: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack].pop();
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToSliderEvents)[_subscribeToSliderEvents]();
	  }
	  set(moduleId, key, value) {
	    const layer = babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack].current();
	    if (!layer.has(moduleId)) {
	      layer.set(moduleId, new Map());
	    }
	    layer.get(moduleId).set(key, value);
	  }
	  delete(moduleId, key) {
	    const moduleData = babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack].current().get(moduleId);
	    if (moduleData) {
	      moduleData.delete(key);
	      if (moduleData.size === 0) {
	        babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack].current().delete(moduleId);
	      }
	    }
	  }
	  getSystem(key) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _collectSystem)[_collectSystem]()[key];
	  }
	  getCustom(moduleId, key) {
	    var _babelHelpers$classPr;
	    return (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack].current().get(moduleId)) == null ? void 0 : _babelHelpers$classPr.get(key);
	  }
	  getModuleCustom(moduleId) {
	    const moduleData = babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack].current().get(moduleId);
	    if (!moduleData) {
	      return {};
	    }
	    return Object.fromEntries(moduleData);
	  }
	  getAllSystem() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _collectSystem)[_collectSystem]();
	  }
	  getAllCustom() {
	    const result = {};
	    for (const [moduleId, moduleData] of babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack].current()) {
	      result[moduleId] = Object.fromEntries(moduleData);
	    }
	    return result;
	  }
	  getAll() {
	    return {
	      system: this.getAllSystem(),
	      custom: this.getAllCustom()
	    };
	  }
	}
	function _collectSystem2() {
	  return {
	    url: getUrl()
	  };
	}
	function _subscribeToSliderEvents2() {
	  main_core_events.EventEmitter.subscribe(SLIDER_EVENT_OPEN, babelHelpers.classPrivateFieldLooseBase(this, _handleSliderOpen)[_handleSliderOpen]);
	  main_core_events.EventEmitter.subscribe(SLIDER_EVENT_CLOSE, babelHelpers.classPrivateFieldLooseBase(this, _handleSliderClose)[_handleSliderClose]);
	}
	const PageContext = new PageContextClass();

	exports.PageContext = PageContext;

}((this.BX.UI.PageContext = this.BX.UI.PageContext || {}),BX.Event));
//# sourceMappingURL=page-context.bundle.js.map
