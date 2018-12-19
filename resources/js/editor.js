// Credit: https://stackoverflow.com/a/5086688
jQuery.fn.insertAt = function (index, element) {
    var lastIndex = this.children().length;
    if (index < 0) {
        index = Math.max(0, lastIndex + 1 + index)
    }
    this.append(element);
    if (index < lastIndex) {
        this.children().eq(index).before(this.children().last())
    }
    return this
};

// Credit: https://coderamblings.wordpress.com/2012/07/09/insert-a-string-at-a-specific-index/
String.prototype.insert = function (index, string) {
    if (index > 0)
        return this.substring(0, index) + string + this.substring(index, this.length);
    else
        return string + this;
};

// initialize the editor when the page itself is finished loading (i.e. "ready")
$(document).ready(function () {
    initEditor()
});

/**
 * Constant strings for various things, such as the default HTML for a new paragraph
 *
 * @type {string[]}
 */
var strings = [
    /* 0 */
    "<div class=\"sub-module paragraph\">",
    /* 1 */
    "<div class=\"module heading\"><span contenteditable=\"true\">New Section</span><span id=\"removesection\"><img src=\"/resources/png/trash.png\" alt=\"remove\" title=\"Remove this section\"></span></div>",
    /* 2 */
    "<div class=\"module section-content\">",
    /* 3 */
    "Ready",
    /* 4 */
    "<div class=\"sub-module list\"><ul>",
    /* 5 */
    "<li contenteditable=\"true\">List item</li>",
    /* 6 */
    "<tr class=\"sub-heading\"><td colspan=\"2\"><span class=\"bold-text\" contenteditable=\"true\">Sub-Heading</span></td></tr>",
    /* 7 */
    "<tr class=\"property\"><th contenteditable=\"true\">Name</th><td id=\"value\" contenteditable=\"true\">Value</td></tr>",
    /* 8 */
    "Saving...",
    /* 9 */
    "e",
    /* 10 */
    "<div class=\"e morph\" contenteditable=\"true\">"
];

// editing indexes
var currentModule = null;
var currentModuleIndex = 0;
var currentParagraph = null;
var currentParagraphIndex = 0;
var currentList = null;
var currentListIndex = 0;
var currentInfoboxIndex = 0;
var currentParagraphElement = null;
var currentParagraphElementIndex = 0;

// link modal
var selectionLength = 0;
var mostRecentCaretPos = 0;
var selectedText = "";
var isDisplayingLinkModal = false;
var mostRecentNode = null;

/**
 * Initializes various elements of the editor, such as toolbar buttons and selection handling
 */
function initEditor() {
    // TOOLBAR BUTTONS ("actionables")
    $(document).on("mouseup", function () {
        $('div.toolbar div.actionable').each(function () {
            $(this).removeClass("actionable-clicked")
        })
    });
    $('div.toolbar div.actionable').on("mousedown", function () {
        $(this).addClass("actionable-clicked")
    });
    $('div.toolbar div.actionable').on("mouseup", function () {
        $(this).removeClass("actionable-clicked")
    });
    $('div.toolbar div.actionable.action-save').on("click", function () {
        action_save()
    });
    $('div.toolbar div.actionable.action-back').on("click", function () {
        window.location = "/dashboard/"
    });
    $('div.toolbar div.actionable.action-newsection').on("click", function () {
        addNewSection(currentModule)
    });
    $('div.toolbar div.actionable.action-newlist').on("click", function () {
        addNewList(currentParagraph)
    });
    $('div.toolbar div.actionable.action-addlink').on("click", function () {
        addNewLink(getSelectionNode())
    });
    $('div.toolbar div.actionable.action-options').on("click", function () {
        // window.location = "/editor/?action=delete&id=" + getHiddenMeta("id")
    });
    $('div.toolbar div.actionable.action-addinfoboxsubheading').on("click", function () {
        insertNewSubHeading()
    });
    $('div.toolbar div.actionable.action-addinfoboxproperty').on("click", function () {
        insertNewProperty()
    });

    // ABSOLUTE EVENTS (amount/element will not change)
    document.onselectionchange = function () {
        if (isDisplayingLinkModal) {
            return
        }
        var s = window.getSelection();
        selectionLength = selectedText.length;
        selectedText = s.toString();
        mostRecentNode = $(":focus");
        mostRecentCaretPos = s.anchorOffset
    };

    // DYNAMIC EVENTS (amount/element can change)
    registerEventHandlers()
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
            setLinkModalVisible(false)
        }
    });
    $("button.insert-link").off("click");
    $("button.insert-link").on("click", function () {
        insertLink(node);
        $("div.link-dialog div.textbox-container input").val("");
        setLinkModalVisible(false)
    })
}

/**
 * Ran when the "Save" toolbar button is clicked/tapped
 */
function action_save() {
    setToolbarSpinnerVisible(true);
    setToolbarStatusText(strings[8]);

    // minimum JSON
    var content = {
        modules: [],
        infobox: {
            heading: "",
            image: {
                file: "",
                caption: ""
            },
            items: []
        }
    };

    $('div.module').each(function () {
        var classlist = this.classList;
        var mod = {
            type: "",
            value: []
        };
        if (classlist.contains("section-content")) {
            mod.type = "section-content";
            $(this).children(".sub-module").each(function () {
                if (this.classList.contains("paragraph")) {
                    mod.value.push({
                        type: "paragraph",
                        value: [{
                            type: "text",
                            value: $(this).html()
                        }]
                    })
                } else {
                    if (this.classList.contains("list")) {
                        var listitems = [];
                        $(this).children().eq(0).children().each(function () {
                            listitems.push($(this).html())
                        });
                        mod.value.push({
                            type: "list",
                            value: listitems
                        })
                    }
                }
            });
        } else if (classlist.contains("heading")) {
            mod.type = "heading";
            mod.value = $(this).children("span").eq(0).html()
        } else {
            mod = null
        }
        if (mod !== null) {
            content.modules.push(mod)
        }
    });

    // infobox image
    content.infobox.heading = $('table.infobox tr.heading td div').html();
    content.infobox.image.file = $("table.infobox tr.main-image table tbody tr td img").attr("src");
    content.infobox.image.caption = $("table.infobox tr.main-image table tbody tr td#caption").html();

    // infobox properties
    $('table.infobox tbody').children().each(function () {
        if (this.className === "property") {
            content.infobox.items.push({
                type: "property",
                label: $(this).children("th").html(),
                value: $(this).children("td#value").html()
            })
        } else if (this.className === "sub-heading") {
            content.infobox.items.push({
                type: "sub-heading",
                value: $(this).children("td").children("span").html()
            })
        }
    });

    // actually save with a POST request to /editor/?action=save
    $.post("/editor/", {
        contentjson: encodeURIComponent(JSON.stringify(content)),
        id: getHiddenMeta("id"),
        isnew: getHiddenMeta("isnew"),
        title: $('div.page-title').html(),
        action: "save"
    }).done(function () {
        setTimeout(function () {
            // timeout to make it look better (too fast!)
            setToolbarSpinnerVisible(false);
            setToolbarStatusText(strings[3])
        }, 1000)
    })
}

/**
 * Registers event handlers for various things, while also clearing any previous declarations
 */
function registerEventHandlers() {
    $('div.paragraph').off('keyup keypress focus');
    $('div.paragraph').each(function () {
        // elements
        $(this).children().each(function () {
            $(this).off('focus keyup keydown');
            $(this).on("focus", function () {
                currentParagraphElement = $(this);
                currentParagraphElementIndex = currentParagraphElement.index();
                currentParagraph = $(this).parent();
                currentParagraphIndex = currentParagraph.index();
                currentModule = $(this).parent().parent();
                currentModuleIndex = currentModule.index()
            });
            $(this).on("keydown", function (e) {
                // prevent spamming of enter key while holding it down
                // this makes enter only do something when it is released
                if ((e.keyCode || e.which) === 13) {
                    e.preventDefault()
                }
            });
            $(this).on("keyup", function (e) {
                var pressedEnter = (e.keyCode || e.which) === 13;
                var pressedBackspace = (e.keyCode || e.which) === 8;
                var isMorph = $(this)[0].classList.contains("morph");
                if (pressedBackspace && mostRecentCaretPos === 0) {
                    var prevElement = $(this).parent().children().eq(currentParagraphElementIndex);
                    if (prevElement !== null) {
                        $(this).remove();
                        prevElement.focus()
                    }
                    return
                }
                if (isMorph) {
                    if (pressedEnter) {
                        // pressed enter in a morph element, therefore make a whole new paragraph instead of just another element
                        addNewParagraph($(this).parent());
                        $(this).remove()
                    } else {
                        // is morph element, but did not press enter, therefore change to plain
                        $(this).attr("class", strings[9] + "plain")
                    }
                } else if (pressedEnter) {
                    addNewParagraphElement($(this));
                    mostRecentCaretPos = 0
                }
            })
        });
        // keyup
        if ($(this) !== $(this).parent().parent().children().eq(1).children().eq(0)) {
            // not the first/intro paragraph, so it can be removed
            $(this).on("keyup", function (e) {
                if (mostRecentCaretPos === 0 && (e.keyCode || e.which) === 8) {
                    // pressed backspace at beginning of paragraph, merge with the one above it
                    // mergeParagraph($(this))
                } else if (isElementHTMLEmpty($(this))) {
                    // $(this).remove()
                }
            })
        }
        // focus
        $(this).on("focus", function () {

        });
        // keypress
        $(this).on("keypress", function (e) {
            if ((e.keyCode || e.which) === 13) {
                e.preventDefault();
                addNewParagraph($(this))
            }
        });
    });

    $('div.module.heading span#removesection').off('click');
    $('div.module.heading span#removesection').on("click", function () {
        var headingModule = $(this).parent();
        // first remove the paragraph container
        headingModule.parent().children().eq(headingModule.index() + 1).remove();
        // then remove the actual heading module
        headingModule.remove()
    });

    $('div.sub-module.list li').off('focus keydown keyup');
    $('div.sub-module.list li').on("focus", function () {
        currentList = $(this).parent();
        currentListIndex = $(this).index();
        currentModule = currentList.parent();
        currentModuleIndex = currentModule.index()
    });
    $('div.sub-module.list li').on("keydown", function () {
        if (event.which === 13 && currentList != null && currentListIndex !== -1) {
            // enter or return
            event.preventDefault();
            insertNewListItem()
        }
    });
    $('div.sub-module.list li').on("keyup", function () {
        if (isElementHTMLEmpty($(this))) {
            var parent = $(this).parent();
            var amount = parent.children().length;
            // remove list item
            $(this).remove();
            if (amount === 1) {
                // remove list if empty
                parent.remove()
            }
        }
    });

    $('table.infobox tr.sub-heading td').off('focus');
    $('table.infobox tr.sub-heading td span').off('keyup');
    $('table.infobox tr.sub-heading td').on("focus", function () {
        currentInfoboxIndex = $(this).parent().index()
    });
    $('table.infobox tr.sub-heading td span').on("keyup", function () {
        if (isElementHTMLEmpty($(this))) {
            $(this).parent().parent().remove()
        }
    });

    $('table.infobox tr.property td').off('focus keyup');
    $('table.infobox tr.property th').off('focus');
    $('table.infobox tr.property td').on("focus", function () {
        currentInfoboxIndex = $(this).parent().index()
    });
    $('table.infobox tr.property th').on("focus", function () {
        currentInfoboxIndex = $(this).parent().index()
    });
    $('table.infobox tr.property td').on("keyup", function () {
        if (isElementHTMLEmpty($(this))) {
            $(this).parent().remove()
        }
    })
}

function setToolbarStatusText(text) {
    $('div.toolbar div.toolbar-status span').html(text)
}

function setToolbarSpinnerVisible(visible) {
    var spin = $('div.toolbar div.toolbar-spinner');
    if (visible) {
        spin.removeClass("hidden")
    } else {
        spin.addClass("hidden")
    }
}

function setLinkModalVisible(visible) {
    if (visible) {
        $("div.link-modal").fadeIn("fast").removeClass("hidden");
        isDisplayingLinkModal = true
    } else {
        $("div.link-modal").fadeOut("fast").addClass("hidden");
        isDisplayingLinkModal = false
    }
}

/**
 * Returns the "node" that has the user selection within it
 *
 * @returns {jQuery|null}
 */
function getSelectionNode() {
    if (selectedText.length === 0) {
        return null
    }
    if (currentModule.hasClass("section-content")) {
        if (currentParagraph == null && currentList == null) {
            return null
        }
        if (currentParagraph == null && currentList != null) {
            return currentList
        }
        if (currentParagraph != null && currentList == null) {
            return currentParagraph
        }
        if (currentParagraph.index() > currentList.index()) {
            return currentList
        } else {
            return currentParagraph
        }
    } else if (currentModule.hasClass("heading")) {
        return currentModule.children().eq(0)
    } else if (currentModule.hasClass("page-title") || currentModule.hasClass("footer")) {
        return null
    } else {
        return null
    }
}

/**
 * Initiates the process of inserting a link, assuming the provided container is not null
 *
 * @param container The container, or "node", that contains the user selection
 */
function addNewLink(container) {
    if (container == null) {
        return
    }
    initLinkDialog(container)
}

function addNewParagraph(p) {
    if (p == null) {
        return
    }
    var container = p.parent();
    container.insertAt(currentParagraphIndex + 1, strings[0] + strings[10] + "</div></div>");
    currentParagraph.html(currentParagraph.html().substring(0, mostRecentCaretPos));
    container.children().eq(currentParagraphIndex + 1).focus();
    registerEventHandlers()
}

function addNewParagraphElement(element) {
    if (element == null) {
        return
    }
    var p = element.parent();
    var prevHTML = element.html();
    p.insertAt(currentParagraphElementIndex + 1, strings[10] + prevHTML.substring(mostRecentCaretPos) + "</div>");
    element.html(prevHTML.substring(0, mostRecentCaretPos));
    p.children().eq(currentParagraphElementIndex + 1).focus();
    registerEventHandlers()
}

function addNewParagraphWithContent(merger, contentHTML) {
    if (merger == null) {
        return
    }
    var container = merger.parent();
    var above = container.children().eq(currentParagraphIndex - 1);
    if (above == null) {
        return
    }
    var originalHTML = above.html();
    above.html(originalHTML + contentHTML);
    merger.remove();
    above.focus();
    registerEventHandlers()
}

/**
 * Combines, or "merges", the given paragraph with the one above it
 *
 * @param toMerge The paragraph, as a jQuery object, to merge
 */
function mergeParagraph(toMerge) {
    addNewParagraphWithContent(toMerge, toMerge.html())
}

function addNewSection(invoker) {
    if (invoker == null) {
        return;
    }
    var allModules = invoker.parent();
    allModules.insertAt(currentModuleIndex + 1, strings[1]);
    allModules.insertAt(currentModuleIndex + 2, strings[2] + strings[0] + "</div>");
    allModules.children().eq(currentModuleIndex + 1).focus();
    registerEventHandlers()
}

function addNewList(invoker) {
    if (invoker == null) {
        return;
    }
    var paragraphContainer = invoker.parent();
    var insertIndex = invoker.index() + 1;
    paragraphContainer.insertAt(insertIndex, strings[4] + strings[5] + "</ul></div>");
    paragraphContainer.children().eq(insertIndex).children().eq(0).focus();
    registerEventHandlers()
}

function insertNewListItem() {
    if (currentListIndex === -1)
        return;
    currentList.insertAt(currentListIndex + 1, strings[5]);
    currentListIndex++;
    currentList.children().eq(currentListIndex).focus();
    registerEventHandlers()
}

function insertNewSubHeading() {
    if (currentInfoboxIndex === -1)
        return;
    $('table.infobox tbody').eq(0).insertAt(currentInfoboxIndex + 1, strings[6]);
    currentInfoboxIndex++;
    $('table.infobox tbody').children().eq(currentInfoboxIndex).children("td").children("span").focus();
    registerEventHandlers()
}

function insertNewProperty() {
    if (currentInfoboxIndex === -1)
        return;
    $('table.infobox tbody').eq(0).insertAt(currentInfoboxIndex + 1, strings[7]);
    currentInfoboxIndex++;
    $('table.infobox tbody').children().eq(currentInfoboxIndex).children("th").focus();
    registerEventHandlers()
}

/**
 * Actually inserts the URL as a link into the provided container at the previously specified location
 *
 * @param container The container, or "node", that contains the user selection
 */
function insertLink(container) {
    // TODO: sanitize url
    if (container === currentParagraph) {
        var originalHTML = container.html();
        // set url to varibale for sanitizing
        var linkAddress = $("div.link-dialog div.textbox-container input").val();
        var pre = "<a rel=\"nofollow\" href=\"" + linkAddress + "\">";
        var post = "</a>";
        mostRecentNode.html(originalHTML.insert(mostRecentCaretPos, pre).insert(mostRecentCaretPos + pre.length + selectionLength, post))
    } else if (container === currentList) {
    } else if (currentModule.hasClass("heading")) {
    } else {
        // TODO: infobox
    }
}
