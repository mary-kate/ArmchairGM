var $ = YAHOO.util.Dom.get;


function $El(name) {
	return new YAHOO.util.Element(name);
}
var $D = YAHOO.util.Dom;
//var $M = YAHOO.util.Anim;
var $E = YAHOO.util.Event;
var $$ = YAHOO.util.Dom.getElementsByClassName;

var Class = {
  create: function() {
    return function() {
      this.initialize.apply(this, arguments);
    }
  }
}
var $A = Array.from = function(iterable) {
  if (!iterable) return [];
  if (iterable.toArray) {
    return iterable.toArray();
  } else {
    var results = [];
    for (var i = 0, length = iterable.length; i < length; i++)
      results.push(iterable[i]);
    return results;
  }
}
Function.prototype.bind = function() {
  var __method = this, args = $A(arguments), object = args.shift();
  return function() {
    return __method.apply(object, args.concat($A(arguments)));
  }
}

YAHOO.util.Dom.getDimensions = function(element){
    element = YAHOO.util.Dom.get(element);
    var display = YAHOO.util.Dom.getStyle( element, 'display');
    if (display != 'none' && display != null) // Safari bug
      return {width: element.offsetWidth, height: element.offsetHeight};

    // All *Width and *Height properties give 0 on elements with display none,
    // so enable the element temporarily
    var els = element.style;
    var originalVisibility = els.visibility;
    var originalPosition = els.position;
    var originalDisplay = els.display;
    els.visibility = 'hidden';
    els.position = 'absolute';
    els.display = 'block';
    var originalWidth = element.clientWidth;
    var originalHeight = element.clientHeight;
    els.display = originalDisplay;
    els.position = originalPosition;
    els.visibility = originalVisibility;
    return {width: originalWidth, height: originalHeight};
}

function Element_Show() { 
	this.setStyle('display', 'block');
	this.setStyle('visibility', 'visible');

}

function Element_Hide() { 
	this.setStyle('display', 'none');
	this.setStyle('visibility', 'hidden');
}

YAHOO.util.Element.prototype.hide = Element_Hide;
YAHOO.util.Element.prototype.show = Element_Show;

YAHOO.util.Element.remove = function(el) {
    element = $(el);
    element.parentNode.removeChild(element);
}

/*
* scriptaculous functionality
*/
YAHOO.widget.Effects = function() {
    return {
        version: '0.8'
    }
}();

YAHOO.widget.Effects.Hide = function(inElm) {
    this.element = YAHOO.util.Dom.get(inElm);

    YAHOO.util.Dom.setStyle(this.element, 'display', 'none');
    YAHOO.util.Dom.setStyle(this.element, 'visibility', 'hidden');
}

YAHOO.widget.Effects.Show = function(inElm) {
    this.element = YAHOO.util.Dom.get(inElm);

    YAHOO.util.Dom.setStyle(this.element, 'display', 'block');
    YAHOO.util.Dom.setStyle(this.element, 'visibility', 'visible');
}

YAHOO.widget.Effects.Fade = function(inElm, opts) {
    this.element = YAHOO.util.Dom.get(inElm);

    var attributes = {
        opacity: { from: 1, to: 0 }
    };
    /**
    * Custom Event fired after the effect completes
    * @type Object
    */
    this.onEffectComplete = new YAHOO.util.CustomEvent('oneffectcomplete', this);

    var ease = ((opts && opts.ease) ? opts.ease : YAHOO.util.Easing.easeOut);
    var secs = ((opts && opts.seconds) ? opts.seconds : 1);
    var delay = ((opts && opts.delay) ? opts.delay : false);

    /**
    * YUI Animation Object
    * @type Object
    */
    this.effect = new YAHOO.util.Anim(this.element, attributes, secs, ease);
    this.effect.onComplete.subscribe(function() {
        YAHOO.widget.Effects.Hide(this.element);
        this.onEffectComplete.fire();
    }, this, true);
    if (!delay) {
        this.effect.animate();
    }
}

YAHOO.widget.Effects.Fade.prototype.animate = function() {
    this.effect.animate();
}

YAHOO.widget.Effects.Appear = function(inElm, opts) {
    this.element = YAHOO.util.Dom.get(inElm);

    YAHOO.util.Dom.setStyle(this.element, 'opacity', '0');
    YAHOO.widget.Effects.Show(this.element);
    var attributes = {
        opacity: { from: 0, to: 1 }
    };
    /**
    * Custom Event fired after the effect completes
    * @type Object
    */
    this.onEffectComplete = new YAHOO.util.CustomEvent('oneffectcomplete', this);
    
    var ease = ((opts && opts.ease) ? opts.ease : YAHOO.util.Easing.easeOut);
    var secs = ((opts && opts.seconds) ? opts.seconds : 3);
    var delay = ((opts && opts.delay) ? opts.delay : false);

    /**
    * YUI Animation Object
    * @type Object
    */
    this.effect = new YAHOO.util.Anim(this.element, attributes, secs, ease);
    this.effect.onComplete.subscribe(function() {
        this.onEffectComplete.fire();
    }, this, true);
    if (!delay) {
        this.effect.animate();
    }
}

YAHOO.widget.Effects.Appear.prototype.animate = function() {
    this.effect.animate();
}


YAHOO.widget.Effects.BlindUp = function(inElm, opts) {
    var ease = ((opts && opts.ease) ? opts.ease : YAHOO.util.Easing.easeOut);
    var secs = ((opts && opts.seconds) ? opts.seconds : 1);
    var delay = ((opts && opts.delay) ? opts.delay : false);
    var ghost = ((opts && opts.ghost) ? opts.ghost : false);

    this.element = YAHOO.util.Dom.get(inElm);
    this._height = $D.getDimensions(this.element).height;
    this._top = parseInt($D.getStyle(this.element, 'top'));
    
    this._opts = opts;

    YAHOO.util.Dom.setStyle(this.element, 'overflow', 'hidden');
    var attributes = {
        height: { to: 0 }
    };
    if (ghost) {
        attributes.opacity = {
            to : 0,
            from: 1
        }
    }

    /**
    * Custom Event fired after the effect completes
    * @type Object
    */
    this.onEffectComplete = new YAHOO.util.CustomEvent('oneffectcomplete', this);


    if (opts && opts.bind && (opts.bind == 'bottom')) {
        var attributes = {
            height: { from: 0, to: parseInt(this._height)},
            top: { from: (this._top + parseInt(this._height)), to: this._top }
        };
        if (ghost) {
            attributes.opacity = {
                to : 1,
                from: 0
            }
        }
    }

    /**
    * YUI Animation Object
    * @type Object
    */
	this.effect = new YAHOO.util.Anim(this.element, attributes, secs, ease);
	this.effect.onComplete.subscribe(function() {
		if (this._opts && this._opts.bind && (this._opts.bind == 'bottom')) {
			YAHOO.util.Dom.setStyle(this.element, 'top', this._top + 'px');
		} else {
			YAHOO.widget.Effects.Hide(this.element);
			YAHOO.util.Dom.setStyle(this.element, 'height', this._height);
		}
		YAHOO.util.Dom.setStyle(this.element, 'opacity', 1);
		this.onEffectComplete.fire();
	}, this, true);
	if (!delay) {
		this.animate();
	}
}
/**
* Preps the style of the element before running the Animation.
*/
YAHOO.widget.Effects.BlindUp.prototype.prepStyle = function() {
    if (this._opts && this._opts.bind && (this._opts.bind == 'bottom')) {
        YAHOO.util.Dom.setStyle(this.element, 'height', '0px');
        YAHOO.util.Dom.setStyle(this.element, 'top', this._height);
    }
    YAHOO.widget.Effects.Show(this.element);
}
/**
* Fires off the embedded Animation.
*/
YAHOO.widget.Effects.BlindUp.prototype.animate = function() {
	this.prepStyle();
	this.effect.animate();
}

YAHOO.widget.Effects.BlindDown = function(inElm, opts) {
    var ease = ((opts && opts.ease) ? opts.ease : YAHOO.util.Easing.easeOut);
    var secs = ((opts && opts.seconds) ? opts.seconds : 1);
    var delay = ((opts && opts.delay) ? opts.delay : false);
    var ghost = ((opts && opts.ghost) ? opts.ghost : false);

    this.element = YAHOO.util.Dom.get(inElm);

    this._opts = opts;
    this._height = parseInt($D.getDimensions(this.element).height );
    this._top = parseInt($D.getStyle(this.element, 'top'));
    
    YAHOO.util.Dom.setStyle(this.element, 'overflow', 'hidden');
    var attributes = {
        height: { from: 0, to: this._height }
    };
    if (ghost) {
        attributes.opacity = {
            to : 1,
            from: 0
        }
    }
    /**
    * Custom Event fired after the effect completes
    * @type Object
    */
    this.onEffectComplete = new YAHOO.util.CustomEvent('oneffectcomplete', this);


    if (opts && opts.bind && (opts.bind == 'bottom')) {
        var attributes = {
            height: { to: 0, from: parseInt(this._height)},
            top: { to: (this._top + parseInt(this._height)), from: this._top }
        };
        if (ghost) {
            attributes.opacity = {
                to : 0,
                from: 1
            }
        }
    }

    /**
    * YUI Animation Object
    * @type Object
    */
    this.effect = new YAHOO.util.Anim(this.element, attributes, secs, ease);

    if (opts && opts.bind && (opts.bind == 'bottom')) {
        this.effect.onComplete.subscribe(function() {
            YAHOO.widget.Effects.Hide(this.element);
            YAHOO.util.Dom.setStyle(this.element, 'top', this._top + 'px');
            YAHOO.util.Dom.setStyle(this.element, 'height', this._height + 'px');
            YAHOO.util.Dom.setStyle(this.element, 'opacity', 1);
            this.onEffectComplete.fire();
        }, this, true);
    } else {
        this.effect.onComplete.subscribe(function() {
            YAHOO.util.Dom.setStyle(this.element, 'opacity', 1);
            this.onEffectComplete.fire();
        }, this, true);
    }
    if (!delay) {
        this.animate();
    }
}
/**
* Preps the style of the element before running the Animation.
*/
YAHOO.widget.Effects.BlindDown.prototype.prepStyle = function() {
    if (this._opts && this._opts.bind && (this._opts.bind == 'bottom')) {
    } else {
        YAHOO.util.Dom.setStyle(this.element, 'height', '0px');
    }
    YAHOO.widget.Effects.Show(this.element);
}
/**
* Fires off the embedded Animation.
*/
YAHOO.widget.Effects.BlindDown.prototype.animate = function() {
    this.prepStyle();
    this.effect.animate();
}


YAHOO.widget.Effects.BlindRight = function(inElm, opts) {
    var ease = ((opts && opts.ease) ? opts.ease : YAHOO.util.Easing.easeOut);
    var secs = ((opts && opts.seconds) ? opts.seconds : 1);
    var delay = ((opts && opts.delay) ? opts.delay : false);
    var ghost = ((opts && opts.ghost) ? opts.ghost : false);
    this.element = YAHOO.util.Dom.get(inElm);

    this._width = parseInt(YAHOO.util.Dom.getStyle(this.element, 'width'));
    this._left = parseInt(YAHOO.util.Dom.getStyle(this.element, 'left'));
    this._opts = opts;

    YAHOO.util.Dom.setStyle(this.element, 'overflow', 'hidden');
    /**
    * Custom Event fired after the effect completes
    * @type Object
    */
    this.onEffectComplete = new YAHOO.util.CustomEvent('oneffectcomplete', this);

    var attributes = {
        width: { from: 0, to: this._width }
    };
    if (ghost) {
        attributes.opacity = {
            to : 1,
            from: 0
        }
    }

    if (opts && opts.bind && (opts.bind == 'right')) {
        var attributes = {
            width: { to: 0 },
            /*left: { from: parseInt, to: this._width }*/
            left: { to: this._left + parseInt(this._width), from: this._left }
        };
        if (ghost) {
            attributes.opacity = {
                to : 0,
                from: 1
            }
        }
    }
    /**
    * YUI Animation Object
    * @type Object
    */
    this.effect = new YAHOO.util.Anim(this.element, attributes, secs, ease);
    if (opts && opts.bind && (opts.bind == 'right')) {
        this.effect.onComplete.subscribe(function() {
            YAHOO.widget.Effects.Hide(this.element);
            YAHOO.util.Dom.setStyle(this.element, 'width', this._width + 'px');
            YAHOO.util.Dom.setStyle(this.element, 'left', this._left + 'px');
            this._width = null;
            YAHOO.util.Dom.setStyle(this.element, 'opacity', 1);
            this.onEffectComplete.fire();
        }, this, true);
    } else {
        this.effect.onComplete.subscribe(function() {
            YAHOO.util.Dom.setStyle(this.element, 'opacity', 1);
            this.onEffectComplete.fire();
        }, this, true);
    }
    if (!delay) {
        this.animate();
    }
}
/**
* Preps the style of the element before running the Animation.
*/
YAHOO.widget.Effects.BlindRight.prototype.prepStyle = function() {
    if (this._opts && this._opts.bind && (this._opts.bind == 'right')) {
    } else {
        YAHOO.util.Dom.setStyle(this.element, 'width', '0');
    }
}
/**
* Fires off the embedded Animation.
*/
YAHOO.widget.Effects.BlindRight.prototype.animate = function() {
    this.prepStyle();
    this.effect.animate();
}

YAHOO.widget.Effects.BlindLeft = function(inElm, opts) {
    var ease = ((opts && opts.ease) ? opts.ease : YAHOO.util.Easing.easeOut);
    var secs = ((opts && opts.seconds) ? opts.seconds : 1);
    var delay = ((opts && opts.delay) ? opts.delay : false);
    var ghost = ((opts && opts.ghost) ? opts.ghost : false);
    this.ghost = ghost;

    this.element = YAHOO.util.Dom.get(inElm);
    this._width = YAHOO.util.Dom.getStyle(this.element, 'width');
    this._left = parseInt(YAHOO.util.Dom.getStyle(this.element, 'left'));


    this._opts = opts;
    YAHOO.util.Dom.setStyle(this.element, 'overflow', 'hidden');
    var attributes = {
        width: { to: 0 }
    };
    if (ghost) {
        attributes.opacity = {
            to : 0,
            from: 1
        }
    }
    
    /**
    * Custom Event fired after the effect completes
    * @type Object
    */
    this.onEffectComplete = new YAHOO.util.CustomEvent('oneffectcomplete', this);


    if (opts && opts.bind && (opts.bind == 'right')) {
        var attributes = {
            width: { from: 0, to: parseInt(this._width) },
            left: { from: this._left + parseInt(this._width), to: this._left }
        };
        if (ghost) {
            attributes.opacity = {
                to : 1,
                from: 0
            }
        }
    }
    
    /**
    * YUI Animation Object
    * @type Object
    */
    this.effect = new YAHOO.util.Anim(this.element, attributes, secs, ease);
    if (opts && opts.bind && (opts.bind == 'right')) {
        this.effect.onComplete.subscribe(function() {
            this.onEffectComplete.fire();
        }, this, true);
    } else {
        this.effect.onComplete.subscribe(function() {
            YAHOO.widget.Effects.Hide(this.element);
            YAHOO.util.Dom.setStyle(this.element, 'width', this._width);
            YAHOO.util.Dom.setStyle(this.element, 'left', this._left + 'px');
            YAHOO.util.Dom.setStyle(this.element, 'opacity', 1);
            this._width = null;
            this.onEffectComplete.fire();
        }, this, true);
    }
    if (!delay) {
        this.animate();
    }
}
/**
* Preps the style of the element before running the Animation.
*/
YAHOO.widget.Effects.BlindLeft.prototype.prepStyle = function() {
    if (this._opts && this._opts.bind && (this._opts.bind == 'right')) {
        YAHOO.widget.Effects.Hide(this.element);
        YAHOO.util.Dom.setStyle(this.element, 'width', '0px');
        YAHOO.util.Dom.setStyle(this.element, 'left', parseInt(this._width));
        if (this.ghost) {
            YAHOO.util.Dom.setStyle(this.element, 'opacity', 0);
        }
        YAHOO.widget.Effects.Show(this.element);
    }
}
/**
* Fires off the embedded Animation.
*/
YAHOO.widget.Effects.BlindLeft.prototype.animate = function() {
    this.prepStyle();
    this.effect.animate();
}


YAHOO.widget.Effects.Pulse = function(inElm, opts) {
    this.element = YAHOO.util.Dom.get(inElm);

    this._counter = 0;
    this._maxCount = 9;
    var attributes = {
        opacity: { from: 1, to: 0 }
    };

    if (opts && opts.maxcount) {
        this._maxCount = opts.maxcount;
    }
    
    /**
    * Custom Event fired after the effect completes
    * @type Object
    */
    this.onEffectComplete = new YAHOO.util.CustomEvent('oneffectcomplete', this);

    var ease = ((opts && opts.ease) ? opts.ease : YAHOO.util.Easing.easeIn);
    var secs = ((opts && opts.seconds) ? opts.seconds : .25);
    var delay = ((opts && opts.delay) ? opts.delay : false);

    /**
    * YUI Animation Object
    * @type Object
    */
    this.effect = new YAHOO.util.Anim(this.element, attributes, secs, ease);
    this.effect.onComplete.subscribe(function() {
        if (this.done) {
            this.onEffectComplete.fire();
        } else {
            if (this._counter < this._maxCount) {
                this._counter++;
                if (this._on) {
                    this._on = null;
                    this.effect.attributes = { opacity: { to: 0 } }
                } else {
                    this._on = true;
                    this.effect.attributes = { opacity: { to: 1 } }
                }
                this.effect.animate();
            } else {
                this.done = true;
                this._on = null;
                this._counter = null;
                this.effect.attributes = { opacity: { to: 1 } }
                this.effect.animate();
            }
        }
    }, this, true);
    if (!delay) {
        this.effect.animate();
    }
}
/**
* Fires off the embedded Animation.
*/
YAHOO.widget.Effects.Pulse.prototype.animate = function() {
    this.effect.animate();
}

/**
* This effect makes the object expand & dissappear.
* @param {String/HTMLElement} inElm HTML element to apply the effect to
* @param {Object} options Pass in an object of options for this effect, you can choose the Easing and the Duration
* <code> <br>var options = (<br>
*   delay: true<br>
*   topOffset: 8<br>
*   leftOffset: 8<br>
*   shadowColor: #ccc<br>
*   shadowOpacity: .75<br>
* )</code>
* @return Animation Object
* @type Object
*/
YAHOO.widget.Effects.Shadow = function(inElm, opts) {
    var delay = ((opts && opts.delay) ? opts.delay : false);
    var topOffset = ((opts && opts.top) ? opts.top : 8);
    var leftOffset = ((opts && opts.left) ? opts.left : 8);
    var shadowColor = ((opts && opts.color) ? opts.color : '#ccc');
    var shadowOpacity = ((opts && opts.opacity) ? opts.opacity : .75);

    this.element = YAHOO.util.Dom.get(inElm);

    
    if (YAHOO.util.Dom.get(this.element.id + '_shadow')) {
        this.shadow = YAHOO.util.Dom.get(this.element.id + '_shadow');
    } else {
        this.shadow = document.createElement('div');
        this.shadow.id = this.element.id + '_shadow';
        this.element.parentNode.appendChild(this.shadow);
    }

    var h = parseInt($T.getHeight(this.element));
    var w = parseInt(YAHOO.util.Dom.getStyle(this.element, 'width'));
    var z = this.element.style.zIndex;
    if (!z) {
        z = 1;
        this.element.style.zIndex = z;
    }

    YAHOO.util.Dom.setStyle(this.element, 'overflow', 'hidden');
    YAHOO.util.Dom.setStyle(this.shadow, 'height', h + 'px');
    YAHOO.util.Dom.setStyle(this.shadow, 'width', w + 'px');
    YAHOO.util.Dom.setStyle(this.shadow, 'background-color', shadowColor);
    YAHOO.util.Dom.setStyle(this.shadow, 'opacity', 0);
    YAHOO.util.Dom.setStyle(this.shadow, 'position', 'absolute');
    this.shadow.style.zIndex = (z - 1);
    var xy = YAHOO.util.Dom.getXY(this.element);

    /**
    * Custom Event fired after the effect completes
    * @type Object
    */
    this.onEffectComplete = new YAHOO.util.CustomEvent('oneffectcomplete', this);
    
    
    var attributes = {
        opacity: { from: 0, to: shadowOpacity },
        top: {
            from: xy[1],
            to: (xy[1] + topOffset)
        },
        left: {
            from: xy[0],
            to: (xy[0] + leftOffset)
        }
    };

    /**
    * YUI Animation Object
    * @type Object
    */
    this.effect = new YAHOO.util.Anim(this.shadow, attributes);
    this.effect.onComplete.subscribe(function() {
        this.onEffectComplete.fire();
    }, this, true);
    if (!delay) {
        this.animate();
    }
}
/**
* Fires off the embedded Animation.
*/
YAHOO.widget.Effects.Shadow.prototype.animate = function() {
    this.effect.animate();
}
/**
* String function for reporting to YUI Logger
*/
YAHOO.widget.Effects.Shadow.prototype.toString = function() {
    return 'Effect Shadow [' + this.element.id + ']';
}



if (!YAHOO.Tools) {
    $T = {
        getHeight: function(el) {
            return YAHOO.util.Dom.getStyle(el, 'height');
        }
    }
}


/**
* @class
* This is a namespace call, nothing here to see.
* @constructor
*/
YAHOO.widget.Effects.ContainerEffect = function() {
}
/**
* @constructor
* Container Effect:<br>
*   Show: BlindUp (binded)<br>
*   Hide: BlindDown (binded)<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindUpDownBinded = function(overlay, dur) {
    var bupdownbinded = new YAHOO.widget.ContainerEffect(overlay, 
        { attributes: {
            effect: 'BlindUp',
            opts: {
                bind: 'bottom'
            }
        },
            duration: dur
        }, {
            attributes: {
                effect: 'BlindDown',
                opts: {
                    bind: 'bottom'
                }
            },
            duration: dur
        },
            overlay.element,
            YAHOO.widget.Effects.Container
        );
    bupdownbinded.init();
    return bupdownbinded;
}
/**
* @constructor
* Container Effect:<br>
*   Show: BlindUp<br>
*   Hide: BlindDown<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindUpDown = function(overlay, dur) {
    var bupdown = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'BlindDown' }, duration: dur }, { attributes: { effect: 'BlindUp' }, duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    bupdown.init();
    return bupdown;
}
/**
* @constructor
* Container Effect:<br>
*   Show: BlindLeft (binded)<br>
*   Hide: BlindRight (binded)<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindLeftRightBinded = function(overlay, dur) {
    var bleftrightbinded = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'BlindLeft', opts: {bind: 'right'} }, duration: dur }, { attributes: { effect: 'BlindRight', opts: { bind: 'right' } }, duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    bleftrightbinded.init();
    return bleftrightbinded;
}
/**
* @constructor
* Container Effect:<br>
*   Show: BlindLeft<br>
*   Hide: BlindRight<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindLeftRight = function(overlay, dur) {
    var bleftright = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'BlindRight' }, duration: dur }, { attributes: { effect: 'BlindLeft' } , duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    bleftright.init();
    return bleftright;
}
/**
* @constructor
* Container Effect:<br>
*   Show: BlindRight<br>
*   Hide: Fold<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindRightFold = function(overlay, dur) {
    var brightfold = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'BlindRight' }, duration: dur }, { attributes: { effect: 'Fold' }, duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    brightfold.init();
    return brightfold;
}
/**
* @constructor
* Container Effect:<br>
*   Show: BlindLeft (binded)<br>
*   Hide: Fold<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindLeftFold = function(overlay, dur) {
    var bleftfold = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'BlindLeft', opts: { bind: 'right' } }, duration: dur }, { attributes: { effect: 'Fold' }, duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    bleftfold.init();
    return bleftfold;
}
/**
* @constructor
* Container Effect:<br>
*   Show: UnFold<br>
*   Hide: Fold<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.UnFoldFold = function(overlay, dur) {
    var bunfold = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'UnFold' }, duration: dur }, { attributes: { effect: 'Fold' }, duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    bunfold.init();
    return bunfold;
}
/**
* @constructor
* Container Effect:<br>
*   Show: BlindDown<br>
*   Hide: BlindDrop<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindDownDrop = function(overlay, dur) {
    var bdowndrop = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'BlindDown' }, duration: dur }, { attributes: { effect: 'Drop' }, duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    bdowndrop.init();
    return bdowndrop;
}

/**
* @constructor
* Container Effect:<br>
*   Show: BlindUp (binded)<br>
*   Hide: BlindDrop<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindUpDrop = function(overlay, dur) {
    var bupdrop = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'BlindUp', opts: { bind: 'bottom' } }, duration: dur }, { attributes: { effect: 'Drop' }, duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    bupdrop.init();
    return bupdrop;
}

/**
* @constructor
* Container Effect:<br>
*   Show: BlindUp (binded)<br>
*   Hide: BlindDown (binded)<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindUpDownBindedGhost = function(overlay, dur) {
    var bupdownbinded = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'BlindUp', opts: {ghost: true, bind: 'bottom' } }, duration: dur }, { attributes: { effect: 'BlindDown', opts: { ghost: true, bind: 'bottom'} }, duration: dur }, overlay.element, YAHOO.widget.Effects.Container);
    bupdownbinded.init();
    return bupdownbinded;
}
/**
* @constructor
* Container Effect:<br>
*   Show: BlindUp<br>
*   Hide: BlindDown<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindUpDownGhost = function(overlay, dur) {
    var bupdown = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'BlindDown', opts: { ghost: true } }, duration: dur }, { attributes: { effect: 'BlindUp', opts: { ghost: true } }, duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    bupdown.init();
    return bupdown;
}
/**
* @constructor
* Container Effect:<br>
*   Show: BlindLeft (binded)<br>
*   Hide: BlindRight (binded)<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindLeftRightBindedGhost = function(overlay, dur) {
    var bleftrightbinded = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'BlindLeft', opts: {bind: 'right', ghost: true } }, duration: dur }, { attributes: { effect: 'BlindRight', opts: { bind: 'right', ghost: true } }, duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    bleftrightbinded.init();
    return bleftrightbinded;
}
/**
* @constructor
* Container Effect:<br>
*   Show: BlindLeft<br>
*   Hide: BlindRight<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindLeftRightGhost = function(overlay, dur) {
    var bleftright = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'BlindRight', opts: { ghost: true } }, duration: dur }, { attributes: { effect: 'BlindLeft', opts: { ghost: true } } , duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    bleftright.init();
    return bleftright;
}
/**
* @constructor
* Container Effect:<br>
*   Show: BlindRight<br>
*   Hide: Fold<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindRightFoldGhost = function(overlay, dur) {
    var brightfold = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'BlindRight', opts: { ghost: true } }, duration: dur }, { attributes: { effect: 'Fold', opts: { ghost: true } }, duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    brightfold.init();
    return brightfold;
}
/**
* @constructor
* Container Effect:<br>
*   Show: BlindLeft (binded)<br>
*   Hide: Fold<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindLeftFoldGhost = function(overlay, dur) {
    var bleftfold = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'BlindLeft', opts: { bind: 'right', ghost: true } }, duration: dur }, { attributes: { effect: 'Fold', opts: { ghost: true } }, duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    bleftfold.init();
    return bleftfold;
}
/**
* @constructor
* Container Effect:<br>
*   Show: UnFold<br>
*   Hide: Fold<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.UnFoldFoldGhost = function(overlay, dur) {
    var bleftfold = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'UnFold', opts: { ghost: true } }, duration: dur }, { attributes: { effect: 'Fold', opts: { ghost: true } }, duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    bleftfold.init();
    return bleftfold;
}

/**
* @constructor
* Container Effect:<br>
*   Show: BlindDown<br>
*   Hide: BlindDrop<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindDownDropGhost = function(overlay, dur) {
    var bdowndrop = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'BlindDown', opts: { ghost: true } }, duration: dur }, { attributes: { effect: 'Drop' }, duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    bdowndrop.init();
    return bdowndrop;
}

/**
* @constructor
* Container Effect:<br>
*   Show: BlindUp (binded)<br>
*   Hide: BlindDrop<br>
* @return Container Effect Object
* @type Object
*/
YAHOO.widget.Effects.ContainerEffect.BlindUpDropGhost = function(overlay, dur) {
    var bupdrop = new YAHOO.widget.ContainerEffect(overlay, { attributes: { effect: 'BlindUp', opts: { bind: 'bottom', ghost: true } }, duration: dur }, { attributes: { effect: 'Drop' }, duration: dur }, overlay.element, YAHOO.widget.Effects.Container );
    bupdrop.init();
    return bupdrop;
}



/**
* @class
* This is a wrapper function to convert my YAHOO.widget.Effect into a YAHOO.widget.ContainerEffects object
* @constructor
* @return Animation Effect Object
* @type Object
*/
YAHOO.widget.Effects.Container = function(el, attrs, dur) {
    var opts = { delay: true };
    if (attrs.opts) {
        for (var i in attrs.opts) {
            opts[i] = attrs.opts[i];
        }
    }
    //var eff = eval('new YAHOO.widget.Effects.' + attrs.effect + '("' + el.id + '", {delay: true' + opts + '})');
    var func = eval('YAHOO.widget.Effects.' + attrs.effect);
    var eff = new func(el, opts);
    
    /**
    * Empty event handler to make ContainerEffects happy<br>
    * May try to attach them to my effects later
    * @type Object
    */
    //eff.onStart = new YAHOO.util.CustomEvent('onstart', this);
    eff.onStart = eff.effect.onStart;
    /**
    * Empty event handler to make ContainerEffects happy<br>
    * May try to attach them to my effects later
    * @type Object
    */
    //eff.onTween = new YAHOO.util.CustomEvent('ontween', this);
    eff.onTween = eff.effect.onTween;
    /**
    * Empty event handler to make ContainerEffects happy<br>
    * May try to attach them to my effects later
    * @type Object
    */
    //eff.onComplete = new YAHOO.util.CustomEvent('oncomplete', this);
    eff.onComplete = eff.onEffectComplete;
    return eff;
}

