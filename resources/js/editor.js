// Credit: https://stackoverflow.com/a/5086688
jQuery.fn.insertAt = function (index, element) {
    var lastIndex = this.children().length;
    if (index < 0) {
        index = Math.max(0, lastIndex + 1 + index);
    }
    this.append(element);
    if (index < lastIndex) {
        this.children().eq(index).before(this.children().last());
    }
    return this;
};

// Credit: https://coderamblings.wordpress.com/2012/07/09/insert-a-string-at-a-specific-index/
String.prototype.insert = function (index, string) {
    if (index > 0) {
        return this.substring(0, index) + string + this.substring(index, this.length);
    } else {
        return string + this;
    }
};

// initialize the editor when the page itself is finished loading (i.e. "ready")
$(document).ready(function () {
    initEditor();
});

var strings = [
    /* 0 */
    '<div class="sub-module paragraph">',
    /* 1 */
    '<div class="module heading"><span contenteditable="true">New Section</span><span id="removesection"><img src="/resources/png/trash.png" alt="remove" title="Remove this section"></span></div>',
    /* 2 */
    '<div class="module section-content">',
    /* 3 */
    "Ready",
    /* 4 */
    '<div class="sub-module list"><ul>',
    /* 5 */
    '<li contenteditable="true">List item</li>',
    /* 6 */
    '<tr class="sub-heading"><td colspan="2"><span class="bold-text" contenteditable="true">Sub-Heading</span></td></tr>',
    /* 7 */
    '<tr class="property"><th contenteditable="true">Name</th><td id="value" contenteditable="true">Value</td></tr>',
    /* 8 */
    "Saving...",
    /* 9 */
    "e",
    /* 10 */
    '<span class="e plain" contenteditable="true">',
    /* 11 */
    '<span class="e morph" contenteditable="true">'
];

// editing
var currentModule = null;
var currentModuleIndex = 0;
var currentList = null;
var currentListIndex = 0;
var currentParagraph = null;
var currentParagraphIndex = 0;
var currentParagraphElement = null;
var currentParagraphElementIndex = 0;
var currentInfoboxIndex = 0;
var currentLinkID = "";
var isInLink = false;

// selection
var selectionLength = 0;
var caretTextIndex = 0;
var caretHTMLIndex = 0;
var selectedText = "";

// modals
var isDisplayingLinkModal = false;

function initEditor() {
    // TOOLBAR BUTTONS ("actionables")
    $(document).on("mouseup", function () {
        $("div.toolbar div.actionable").each(function () {
            $(this).removeClass("actionable-clicked");
        });
    });
    $("div.toolbar div.actionable").on("mousedown", function () {
        $(this).addClass("actionable-clicked");
    });
    $("div.toolbar div.actionable").on("mouseup", function () {
        $(this).removeClass("actionable-clicked");
    });
    $("div.toolbar div.actionable.action-save").on("click", function () {
        action_save();
    });
    $("div.toolbar div.actionable.action-back").on("click", function () {
        window.location = "/dashboard/";
    });
    $("div.toolbar div.actionable.action-newsection").on("click", function () {
        addNewSection(currentModule);
    });
    $("div.toolbar div.actionable.action-newlist").on("click", function () {
        addNewList(currentParagraph);
    });
    $("div.toolbar div.actionable.action-addlink").on("click", function () {
        addNewLink(getFocusedElement());
    });
    $("div.toolbar div.actionable.action-options").on("click", function () {
        /* window.location = "/editor/?action=delete&id=" + getHiddenMeta("id")*/
    });
    $("div.toolbar div.actionable.action-addinfoboxsubheading").on("click", function () {
        insertNewSubHeading();
    });
    $("div.toolbar div.actionable.action-addinfoboxproperty").on("click", function () {
        insertNewProperty();
    });

    // ABSOLUTE EVENTS (amount/element will not change)
    document.onselectionchange = function () {
        if (isDisplayingLinkModal) {
            return;
        }
        var s = window.getSelection();
        selectionLength = selectedText.length;
        selectedText = s.toString();
        caretTextIndex = getAbsoluteCaretPos(s, true);
        caretHTMLIndex = getAbsoluteCaretPos(s, false);
    };

    // DYNAMIC EVENTS (amount/element can change)
    registerEventHandlers();
}

function getAbsoluteCaretPos(s, textonly) {
    var me = s.anchorNode;
    var parent = me.parentNode;
    if (parent.nodeName.length <= 2) {
        if (parent.nodeName.toLowerCase() === "a") {
            isInLink = true;
            onLinkFocus(parent)
        } else {
            isInLink = false
        }
        // probably <b>, <i> or <a> element (as opposed to normal <div> or <span>), therefore go "up" one "level"
        me = me.parentNode;
        parent = me.parentNode;
    } else {
        isInLink = false
    }
    var childs = parent.childNodes;
    var myIndex = -1;
    var allbeforelength = 0;
    for (var i = 0; i < childs.length; i++) {
        if (childs[i].isSameNode(me)) {
            myIndex = i;
            break;
        }
    }
    for (var i = 0; i <= myIndex - 1; i++) {
        var c = childs[i];
        if (textonly || (!textonly && c.nodeType !== Node.ELEMENT_NODE)) {
            allbeforelength += c.textContent.length;
        } else {
            allbeforelength += c.outerHTML.length;
        }
    }
    allbeforelength += me.textContent.substring(0, s.anchorOffset).length;
    return allbeforelength;
}

function initLinkDialog(node) {
    setLinkModalVisible(true);
    // unfocus container
    node.trigger("blur");
    // focus dialog, specifically textbox
    $("div.link-modal div.link-dialog div.textbox-container input").trigger("focus");

    $("div.link-modal").off("click");
    $("div.link-modal").on("click", function (e) {
        // hide if click was not anywhere in the dialog
        if ($(e.target).hasClass("link-modal")) {
            // only change visibility if clicked on modal itself, not dialog
            setLinkModalVisible(false);
        }
    });
    $("button.insert-link").off("click");
    $("button.insert-link").on("click", function () {
        insertLink(node);
        $("div.link-dialog div.textbox-container input").val("");
        setLinkModalVisible(false);
    });
}

function action_save() {
    setToolbarSpinnerVisible(true);
    setToolbarStatusText(strings[8]);

    // minimum JSON
    var content = {modules: [], infobox: {heading: "", image: {file: "", caption: ""}, items: []}};

    $("div.module").each(function () {
        var classlist = this.classList;
        var mod = {type: "", value: []};
        if (classlist.contains("section-content")) {
            mod.type = "section-content";
            $(this).children(".sub-module").each(function () {
                if (this.classList.contains("paragraph")) {
                    var elements = [];
                    $(this).children().each(function () {
                        if (this.classList.contains("plain")) {
                            elements.push({type: "plain", value: $(this).html()});
                        } else if (this.classList.contains("link")) {
                            elements.push({
                                type: "link",
                                value: {displaytext: $(this).html(), url: $(this).attr("url")}
                            });
                        }
                    });
                    mod.value.push({type: "paragraph", value: elements});
                } else if (this.classList.contains("list")) {
                    var listitems = [];
                    $(this).children().eq(0).children().each(function () {
                        listitems.push($(this).html());
                    });
                    mod.value.push({type: "list", value: listitems});
                }
            });
        } else if (classlist.contains("heading")) {
            mod.type = "heading";
            mod.value = $(this).children("span").eq(0).html();
        } else {
            mod = null;
        }
        if (mod !== null) {
            content.modules.push(mod);
        }
    });

    // infobox image
    content.infobox.heading = $("table.infobox tr.heading td div").html();
    content.infobox.image.file = $(
        "table.infobox tr.main-image table tbody tr td img"
    ).attr("src");
    content.infobox.image.caption = $(
        "table.infobox tr.main-image table tbody tr td#caption"
    ).html();

    // infobox properties
    $("table.infobox tbody")
        .children()
        .each(function () {
            if (this.className === "property") {
                content.infobox.items.push({
                    type: "property",
                    label: $(this)
                        .children("th")
                        .html(),
                    value: $(this)
                        .children("td#value")
                        .html()
                });
            } else if (this.className === "sub-heading") {
                content.infobox.items.push({
                    type: "sub-heading",
                    value: $(this)
                        .children("td")
                        .children("span")
                        .html()
                });
            }
        });

    // actually save with a POST request to /editor/?action=save
    $.post("/editor/", {
        contentjson: encodeURIComponent(JSON.stringify(content)),
        id: getHiddenMeta("id"),
        isnew: getHiddenMeta("isnew"),
        title: $("div.page-title").html(),
        action: "save"
    }).done(function () {
        setTimeout(function () {
            // timeout to make it look better (too fast!)
            setToolbarSpinnerVisible(false);
            setToolbarStatusText(strings[3]);
        }, 1000);
    });
}

function registerEventHandlers() {
    // TODO: only update events for the modified/added module(s) instead of the entire page (prevent performance issues with large amounts of content)

    $("div.paragraph").off("keyup keypress focus");
    $("div.paragraph").each(function () {
        // this paragraph's elements
        $(this).children().each(function () {
            $(this).off("focus keyup keydown");
            $(this).on("focus", function () {
                // element (this)
                currentParagraphElement = $(this);
                currentParagraphElementIndex = currentParagraphElement.index();
                // element -> paragraph (aka section content)
                currentParagraph = $(this).parent();
                currentParagraphIndex = currentParagraph.index();
                // element -> paragraph (aka section content) -> module
                currentModule = $(this).parent().parent();
                currentModuleIndex = currentModule.index();
            });
            $(this).on("keydown", function (e) {
                // prevent spamming of enter key while holding it down
                // this makes enter only do something when it is released
                if ((e.keyCode || e.which) === 13) {
                    e.preventDefault();
                }
            });
            $(this).on("keyup", function (e) {
                var enter = (e.keyCode || e.which) === 13;
                var backspace = (e.keyCode || e.which) === 8;
                var isMorph = $(this)[0].classList.contains("morph");
                var empty = isElementHTMLEmpty($(this));
                if (backspace) {
                    if (empty && caretHTMLIndex === 0) {
                        // check to make sure this is not the first paragraph/element in the entire page
                        if (currentParagraphIndex === 0) {
                            return
                        }
                        // delete this element, knowing that it's not the first paragraph/element in the page
                        var preceding = $(this).parent().children().eq(currentParagraphElementIndex - 1);
                        if (preceding !== null) {
                            $(this).remove();
                            preceding.focus();
                        }
                    } else if (empty) {
                        // merge with above paragraph or element
                        if (currentParagraphElementIndex === 0) {
                            // backspace-ing in the first element, so merge into the above paragraph
                            mergeParagraph($(this).parent());
                            resetCaretIndexes();
                        } else {
                            // backspace-ing NOT in the first element, so merge into the above element (same paragraph)
                            mergeParagraphElement($(this));
                            resetCaretIndexes();
                        }
                    }
                    return;
                }
                // did not press backspace at beginning of an empty element
                if (isMorph) {
                    $(this).attr("class", strings[9] + " plain");
                    if (enter) {
                        // pressed enter in a morph element, therefore make a whole new paragraph instead of just another element
                        addNewParagraph($(this).parent());
                    }
                } else if (enter) {
                    // pressed enter in a non-morph element
                    if (selectionLength > 0) {
                        // this actually worked on the first try... wow (thanks coffee!)
                        var prev = $(this).html();
                        var replace = $(this).html().substring(caretHTMLIndex).replace(selectedText, "");
                        $(this).html(prev.substring(0, caretHTMLIndex) + replace);
                    }
                    addNewParagraphElement($(this));
                    resetCaretIndexes();
                }
            });
        });
    });

    $("div.module.heading span#removesection").off("click");
    $("div.module.heading span#removesection").on("click", function () {
        var headingModule = $(this).parent();
        // first remove the paragraph container (i.e. section content)
        headingModule.parent().children().eq(headingModule.index() + 1).remove();
        // then remove the actual heading (i.e. section title)
        headingModule.remove();
    });

    $("div.sub-module.list li").off("focus keydown keyup");
    $("div.sub-module.list li").on("focus", function () {
        currentList = $(this).parent();
        currentListIndex = $(this).index();
        currentModule = currentList.parent();
        currentModuleIndex = currentModule.index();
    });
    $("div.sub-module.list li").on("keydown", function () {
        if (event.which === 13 && currentList != null && currentListIndex !== -1) {
            // enter or return
            event.preventDefault();
            insertNewListItem();
        }
    });
    $("div.sub-module.list li").on("keyup", function () {
        if (isElementHTMLEmpty($(this))) {
            var parent = $(this).parent();
            var amount = parent.children().length;
            // remove list item
            $(this).remove();
            if (amount === 1) {
                // remove list if empty
                parent.remove();
            }
        }
    });

    $("table.infobox tr.sub-heading td").off("focus");
    $("table.infobox tr.sub-heading td span").off("keyup");
    $("table.infobox tr.sub-heading td").on("focus", function () {
        currentInfoboxIndex = $(this).parent().index();
    });
    $("table.infobox tr.sub-heading td span").on("keyup", function () {
        if (isElementHTMLEmpty($(this))) {
            $(this).parent().parent().remove();
        }
    });

    $("table.infobox tr.property td").off("focus keyup");
    $("table.infobox tr.property th").off("focus");
    $("table.infobox tr.property td").on("focus", function () {
        currentInfoboxIndex = $(this).parent().index();
    });
    $("table.infobox tr.property th").on("focus", function () {
        currentInfoboxIndex = $(this).parent().index();
    });
    $("table.infobox tr.property td").on("keyup", function () {
        if (isElementHTMLEmpty($(this))) {
            $(this).parent().remove();
        }
    });
}

function resetCaretIndexes() {
    caretTextIndex = 0;
    caretHTMLIndex = 0;
}

function setToolbarStatusText(text) {
    $("div.toolbar div.toolbar-status span").html(text);
}

function setToolbarSpinnerVisible(visible) {
    var spin = $("div.toolbar div.toolbar-spinner");
    if (visible) {
        spin.removeClass("hidden");
    } else {
        spin.addClass("hidden");
    }
}

function setLinkModalVisible(visible) {
    if (visible) {
        $("div.link-modal").fadeIn("fast").removeClass("hidden");
        isDisplayingLinkModal = true;
    } else {
        $("div.link-modal").fadeOut("fast").addClass("hidden");
        isDisplayingLinkModal = false;
    }
}

function getFocusedElement() {
    if (currentModule.hasClass("section-content")) {
        if (currentParagraph == null && currentList == null) {
            return null;
        }
        if (currentParagraph == null && currentList != null) {
            return currentList.children().eq(currentListIndex);
        }
        if (currentParagraph != null && currentList == null) {
            return currentParagraph.children().eq(currentParagraphElementIndex);
        }
        if (currentParagraph.index() > currentList.index()) {
            return currentList.children().eq(currentListIndex);
        } else {
            return currentParagraph.children().eq(currentParagraphElementIndex);
        }
    } else if (currentModule.hasClass("heading")) {
        return currentModule.children().eq(0);
    } else if (
        currentModule.hasClass("page-title") ||
        currentModule.hasClass("footer")
    ) {
        return null;
    } else {
        return null;
    }
}

function onLinkFocus(elementNode) {
    // wrap/convert to jQuery object for easier use
    var link = $(elementNode);
    if (link.attr("id") !== undefined) {
        currentLinkID = link.attr("id")
    } else {
        currentLinkID = ""
    }
}

function removeLinkFrom(html, link) {
    return html.replace(link.prop("outerHTML"), link.html());
}

function removeLink(html) {
    return removeLinkFrom(html, $("a#" + currentLinkID))
}

function addNewLink(container) {
    if (container == null) {
        return
    }
    initLinkDialog(container);
}

function addNewParagraph(p) {
    if (p == null) {
        return
    }
    var container = p.parent();
    var elementHTML = currentParagraphElement.html();

    currentParagraphElement.remove();
    container.insertAt(currentParagraphIndex + 1, strings[0] + strings[10] + elementHTML + "</span></div>");
    container.children().eq(currentParagraphIndex + 1).children().eq(0).focus();

    currentParagraph = container.children().eq(currentParagraphIndex + 1);
    currentParagraphIndex = currentParagraph.index();
    currentParagraphElement = currentParagraph.children().eq(0);
    currentParagraphElementIndex = 0;

    registerEventHandlers();
}

function addNewParagraphElement(element) {
    if (element == null) {
        return
    }
    var paragraph = element.parent();
    var html = element.html();

    // check to see if user pressed enter inside a link
    if (isInLink) {
        // remove it
        html = removeLink(html)
    }

    var beforeHTML = html.substring(0, caretHTMLIndex);
    var afterHTML = html.substring(caretHTMLIndex);

    paragraph.insertAt(currentParagraphElementIndex + 1, strings[11] + afterHTML + "</span>");
    element.html(beforeHTML);
    paragraph.children().eq(currentParagraphElementIndex + 1).focus();

    currentParagraphElement = paragraph.children().eq(currentParagraphElementIndex + 1);
    currentParagraphElementIndex = currentParagraphElement.index();

    registerEventHandlers();
}

function mergeParagraph(toMerge) {
    if (currentParagraphIndex === 0) {
        // can't merge into non-existent paragraph (there is not one before it)
        console.log("cannot merge " + Math.random());
        return;
    }
    var html = toMerge.html();
    var paragraphAbove = toMerge.parent().children().eq(currentParagraphIndex - 1);
    paragraphAbove.html(paragraphAbove.html() + html);
    toMerge.remove();
    currentParagraph = paragraphAbove;
    currentParagraphIndex = currentParagraph.index();
    currentParagraphElement = currentParagraph.children().eq(currentParagraph.children().length - 1);
    currentParagraphElementIndex = currentParagraphElement.index();
    currentParagraphElement.focus();
    registerEventHandlers();
}

function mergeParagraphElement(toMerge) {
    if (currentParagraphElementIndex === 0) {
        // can't merge into non-existent element (there is not one before it)
        return;
    }
    var html = toMerge.html();
    var aboveElement = toMerge.parent().children().eq(currentParagraphElementIndex - 1);
    var aboveHTML = aboveElement.html();
    aboveElement.html(aboveHTML + html);
    toMerge.remove();
    currentParagraphElement = aboveElement;
    currentParagraphElementIndex = currentParagraphElement.index();
    currentParagraphElement.focus();
    registerEventHandlers();
}

function addNewSection(invoker) {
    if (invoker == null) {
        return;
    }
    var allModules = invoker.parent();
    allModules.insertAt(currentModuleIndex + 1, strings[1]);
    allModules.insertAt(currentModuleIndex + 2, strings[2] + strings[0] + "</div>");
    allModules.children().eq(currentModuleIndex + 1).focus();
    registerEventHandlers();
}

function addNewList(invoker) {
    if (invoker == null) {
        return;
    }
    var paragraphContainer = invoker.parent();
    var insertIndex = invoker.index() + 1;
    paragraphContainer.insertAt(insertIndex, strings[4] + strings[5] + "</ul></div>");
    paragraphContainer.children().eq(insertIndex).children().eq(0).focus();
    registerEventHandlers();
}

function insertNewListItem() {
    if (currentListIndex === -1) return;
    currentList.insertAt(currentListIndex + 1, strings[5]);
    currentListIndex++;
    currentList.children().eq(currentListIndex).focus();
    registerEventHandlers();
}

function insertNewSubHeading() {
    if (currentInfoboxIndex === -1) {
        return
    }
    $("table.infobox tbody").eq(0).insertAt(currentInfoboxIndex + 1, strings[6]);
    currentInfoboxIndex++;
    $("table.infobox tbody").children().eq(currentInfoboxIndex).children("td").children("span").focus();
    registerEventHandlers();
}

function insertNewProperty() {
    if (currentInfoboxIndex === -1) {
        return
    }
    $("table.infobox tbody").eq(0).insertAt(currentInfoboxIndex + 1, strings[7]);
    currentInfoboxIndex++;
    $("table.infobox tbody").children().eq(currentInfoboxIndex).children("th").focus();
    registerEventHandlers();
}

function insertLink(element) {
    var url = $("div.link-dialog div.textbox-container input").val();
    // Credit: https://stackoverflow.com/a/8234912
    var re = /((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=+$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=+$,\w]+@)[A-Za-z0-9.-]+)((?:\/[+~%\/.\w-_]*)?\??(?:[-+=&;%@.\w_]*)#?(?:[\w]*))?)/;
    if (!re.exec(url)) {
        console.log("Invaild URL! (" + url + ")");
        // TODO: add option to allow "dirty", or unvalidated, URLs (but have it off by default)
        return
    }
    var elementHTML = element.html();
    var linkID = Date.now().toString();
    var insert = '<a rel="nofollow" href="' + url + '" id="' + linkID + '">' + selectedText + '</a>';
    var replaced = elementHTML.substring(caretTextIndex).replace(selectedText, insert);
    var newHTML = elementHTML.substring(0, caretTextIndex) + replaced;
    element.html(newHTML);
}
