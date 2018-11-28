// https://stackoverflow.com/a/5086688
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

// https://coderamblings.wordpress.com/2012/07/09/insert-a-string-at-a-specific-index/
String.prototype.insert = function (index, string) {
    if (index > 0)
        return this.substring(0, index) + string + this.substring(index, this.length);
    else
        return string + this;
};

$(document).ready(function () {
    initEditor()
});

var strings = [
    /* 0 */
    "<div class=\"sub-module paragraph\" contenteditable=\"true\">This is a paragraph. Click or tap to change its text.</div>",
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
    "Saving..."
];

// editing indexes
var currentModule = null;
var currentModuleIndex = 0;
var currentParagraph = null;
var currentParagraphIndex = 0;
var currentList = null;
var currentListIndex = 0;
var currentInfoboxIndex = 0;

// link modal
var selectionLength = 0;
var mostRecentCaretPos = 0;
var previousCaretPos = 0;
var selectedText = "";
var isDisplayingLinkModal = false;
var totalIndexOffset = 0;

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
    $('div.toolbar div.actionable.action-newparagraph').on("click", function () {
        addNewParagraph(currentParagraph)
    });
    $('div.toolbar div.actionable.action-newsection').on("click", function () {
        addNewSection(currentModule)
    });
    $('div.toolbar div.actionable.action-newlist').on("click", function () {
        addNewList(currentParagraph)
    });
    $('div.toolbar div.actionable.action-addlink').on("click", function () {
        addNewLink(getSelectionContainingElement())
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
    // $('tr.main-image img').click(function() {
    // open image picker or something like that
    // });
    document.onselectionchange = function () {
        if (isDisplayingLinkModal) {
            return
        }
        var s = window.getSelection();
        selectionLength = selectedText.length;
        selectedText = s.toString();
        mostRecentCaretPos = totalIndexOffset + s.anchorOffset + previousCaretPos
    };

    // DYNAMIC EVENTS (amount/element can change)
    registerEventHandlers()
}

function initLinkDialog(container) {
    setLinkModalVisible(true);
    // unfocus container
    container.trigger("blur");
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
        insertLink(container);
        $("div.link-dialog div.textbox-container input").val("");
        setLinkModalVisible(false)
    })
}

function action_save() {
    setToolbarSpinnerVisible(true);
    setToolbarStatusText(strings[8]);

    // save
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

    content.infobox.heading = $('table.infobox tr.heading td div').html();
    content.infobox.image.file = $("table.infobox tr.main-image table tbody tr td img").attr("src");
    content.infobox.image.caption = $("table.infobox tr.main-image table tbody tr td#caption").html();

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

function registerEventHandlers() {
    $('div.paragraph').off('keyup focus');
    $('div.paragraph').each(function () {
        // keyup
        if ($(this).html() !== $(this).parent().parent().children().eq(1).children().eq(0).html()) {
            // not the first/intro paragraph, so it can be removed
            $(this).on("keyup", function () {
                if (isElementHTMLEmpty($(this))) {
                    $(this).remove()
                }
            })
        }
        // focus
        $(this).on("focus", function () {
            currentModule = $(this).parent();
            currentModuleIndex = currentModule.index();
            currentParagraph = $(this);
            // currentParagraph.addClass("editing-infocus");
            currentParagraphIndex = $(this).index()
        })
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
    });

    $('div.page-content *').each(function () {
        var attr = $(this).attr("contenteditable");
        if (typeof attr !== typeof undefined && attr !== false) {
            $(this).on("click", function (event) {
                // checkForLinkModalFix()
            })
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

function getSelectionContainingElement() {
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

function addNewLink(container) {
    if (container == null) {
        return
    }
    initLinkDialog(container)
}

function addNewParagraph(invoker) {
    if (invoker == null) {
        return;
    }
    var container = invoker.parent();
    container.insertAt(currentParagraphIndex + 1, strings[0]);
    container.children().eq(currentParagraphIndex + 1).focus();
    registerEventHandlers()
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

function insertLink(container) {
    // TODO: sanitize url
    if (container === currentParagraph) {
        var originalHTML = container.html();
        // set url to varibale for sanitizing
        var linkAddress = $("div.link-dialog div.textbox-container input").val();
        var pre = "<a rel=\"nofollow\" href=\"" + linkAddress + "\">";
        var post = "</a>";
        var modifiedHTML = originalHTML.insert(mostRecentCaretPos, pre);
        modifiedHTML = modifiedHTML.insert(mostRecentCaretPos + pre.length + selectionLength, post);
        container.html(modifiedHTML);
        console.log("prev: " + previousCaretPos + ", recent: " + mostRecentCaretPos + ", total: " + totalIndexOffset + ", sel len: " + selectionLength);
        totalIndexOffset += selectionLength;
        previousCaretPos = mostRecentCaretPos
    } else if (container === currentList) {
    } else if (currentModule.hasClass("heading")) {
    } else {
        // TODO: infobox stuff
    }
}
