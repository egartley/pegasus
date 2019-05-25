// functions for the page editor, see initEditor()

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

var selectedText = "";
var selectionLength = 0;
var caretTextIndex = 0;
var caretHTMLIndex = 0;

var isDisplayingModal = false;
var isDisplayingLinkHoverer = false;
var finishedShowingLinkHoverer = false;

function initEditor() {
    // TOOLBAR BUTTONS
    $("div.toolbar div.actionable").on("mouseup mouseleave", function () {
        $(this).removeClass("actionable-clicked")
    });
    $("div.toolbar div.actionable").on("mousedown", function () {
        $(this).addClass("actionable-clicked")
    });
    $("div.toolbar div.actionable.action-livepage").on("click", function () {
        if (!$(this).hasClass("actionable-disabled")) {
            window.open(getHiddenMeta("livepath"), '_blank');
        }
    });
    $("div.toolbar div.actionable.action-save").on("click", function () {
        if (!$(this).hasClass("actionable-disabled")) {
            action_save();
        }
    });
    $("div.toolbar div.actionable.action-back").on("click", function () {
        if (!$(this).hasClass("actionable-disabled")) {
            window.location = "/dashboard/";
        }
    });
    $("div.toolbar div.actionable.action-newsection").on("click", function () {
        if (!$(this).hasClass("actionable-disabled")) {
            addNewSection(currentModule);
        }
    });
    $("div.toolbar div.actionable.action-newlist").on("click", function () {
        if (!$(this).hasClass("actionable-disabled")) {
            addNewList(currentParagraph)
        }
    });
    $("div.toolbar div.actionable.action-addlink").on("click", function () {
        if (!$(this).hasClass("actionable-disabled")) {
            addNewLink(getFocusedElement())
        }
    });
    $("div.toolbar div.actionable.action-options").on("click", function () {
        initOptionsDialog(getFocusedElement())
    });
    $("div.toolbar div.actionable.action-addinfoboxsubheading").on("click", function () {
        if (!$(this).hasClass("actionable-disabled")) {
            insertNewSubHeading()
        }
    });
    $("div.toolbar div.actionable.action-addinfoboxproperty").on("click", function () {
        if (!$(this).hasClass("actionable-disabled")) {
            insertNewProperty()
        }
    });

    // disable these buttons because there is initially no context and current elements/indexes are null or 0
    actionButton_disable("newsection", "plus");
    actionButton_disable("newlist", "list");
    actionButton_disable("addlink", "link");
    actionButton_disable("addinfoboxsubheading", "plus");
    actionButton_disable("addinfoboxproperty", "plus");

    // ABSOLUTE EVENTS
    document.onselectionchange = function () {
        if (isDisplayingModal || isDisplayingLinkHoverer) {
            return;
        }
        var s = window.getSelection();
        selectedText = s.toString();
        selectionLength = selectedText.length;
        caretTextIndex = getAbsoluteCaretPos(s, true);
        caretHTMLIndex = getAbsoluteCaretPos(s, false);

        // determine context
        if (s.anchorNode.parentNode.nodeName === "TD" || s.anchorNode.parentNode.nodeName === "TH" || s.anchorNode.parentNode.parentNode.nodeName === "TD") {
            context_infobox()
        } else {
            context_content();
        }
    };

    // DIALOG/MODAL EVENTS
    $("div.link-hoverer span button#apply").on("click", function () {
        var url = $("div.link-hoverer input#linkURL").val();
        if (!isURLSanitary(url)) {
            return
        }
        $("div.sub-module.paragraph .e a#" + currentLinkID).attr("href", url)
    });
    $("div.link-hoverer span button#remove").on("click", function () {
        removeCurrentLink();
        setLinkHovererVisible(false);
        registerEventHandlers()
    });
    $("div.link-hoverer span input#newtab").on("click", function () {
        var link = $("a#" + currentLinkID);
        if (link.attr("target") === "_default") {
            link.attr("target", "_blank")
        } else {
            link.attr("target", "_default")
        }
    });

    $("div.options-dialog div.dialog-content button#slugapply").on("click", function () {
        // TODO: sanitize input
        // get the inputted slug
        var newslug = $("div.options-dialog div.dialog-content input#sluginput").val();
        // set it to hidden meta
        setHiddenMeta("slug", newslug);
        $("div.options-dialog div.dialog-content span#slugstatustext").html("Submitting new slug...");
        $.post("/submit/", {
            action: "updateslug",
            id: getHiddenMeta("id"),
            value: newslug,
            savemeta: "yes"
        }).done(function (data) {
            $("div.options-dialog div.dialog-content span#slugstatustext").html(data)
        });
    });

    // OTHER EVENTS
    $(window).scroll(function () {
        onWindowScroll()
    });

    // DYNAMIC EVENTS (amount/element can change)
    registerEventHandlers();
}

function context_infobox() {
    actionButton_disable("newsection", "plus");
    actionButton_disable("newlist", "list");
    actionButton_disable("addlink", "link");
    actionButton_enable("addinfoboxsubheading", "plus");
    actionButton_enable("addinfoboxproperty", "plus");
}

function context_content() {
    actionButton_enable("newsection", "plus");
    actionButton_enable("newlist", "list");
    if (selectionLength !== 0) {
        actionButton_enable("addlink", "link");
    } else {
        actionButton_disable("addlink", "link")
    }
    actionButton_disable("addinfoboxsubheading", "plus");
    actionButton_disable("addinfoboxproperty", "plus");
}

function getAbsoluteCaretPos(s, textonly) {
    var me = s.anchorNode;
    if (me === null) {
        return
    }
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

// Credit: https://stackoverflow.com/a/6249440
/*function setCaretPosFromAfterMerge(nowinfocus, offset) {
    var range = document.createRange();
    var sel = window.getSelection();
    range.setStart(nowinfocus, offset - 1);
    range.collapse(true);
    sel.removeAllRanges();
    sel.addRange(range)
}*/

function shortcut_k() {
    if (selectionLength === 0 || selectedText === "") {
        return
    }
    addNewLink(getFocusedElement())
}

function shortcut_b() {
    if (selectionLength === 0 || selectedText === "") {
        return
    }
    insertBold(getFocusedElement())
}

function shortcut_i() {
    if (selectionLength === 0 || selectedText === "") {
        return
    }
    insertItalics(getFocusedElement())
}

function initLinkDialog(focusedelement) {
    setLinkModalVisible(true);
    // unfocus whatever was focused before
    focusedelement.trigger("blur");
    // focus dialog, specifically textbox
    $("div.link-modal div.link-dialog div.dialog-content div.textbox-container input").trigger("focus");

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
        insertLink(focusedelement);
        $("div.link-dialog div.dialog-content div.textbox-container input").val("");
        setLinkModalVisible(false)
    });
}

function initOptionsDialog(focusedelement) {
    setOptionsModalVisible(true);
    if (focusedelement !== null) {
        focusedelement.trigger("blur")
    }
    $("div.options-modal div.options-dialog div.dialog-title span").trigger("focus");
    $("div.options-modal").off("click");
    $("div.options-modal").on("click", function (e) {
        // hide if click was not anywhere in the dialog
        if ($(e.target).hasClass("options-modal")) {
            // only change visibility if clicked on modal itself, not dialog
            setOptionsModalVisible(false);
        }
    });
    // clear statuses
    $("div.options-dialog div.dialog-content span#slugstatustext").html()
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
                if ($(this)[0].classList.contains("paragraph")) {
                    var elements = [];
                    $(this).children().each(function () {
                        if ($(this)[0].classList.contains("plain")) {
                            elements.push({type: "plain", value: $(this).html()});
                        } else if ($(this)[0].classList.contains("link")) {
                            elements.push({
                                type: "link",
                                value: {displaytext: $(this).html(), url: $(this).attr("url")}
                            });
                        }
                    });
                    mod.value.push({type: "paragraph", value: elements});
                } else if ($(this)[0].classList.contains("list")) {
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
        slug: getHiddenMeta("slug"),
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

function key(e, code) {
    return (e.keyCode || e.which) === code
}

function onWindowScroll() {
    if (isDisplayingLinkHoverer) {
        setLinkHovererVisible(false, "")
    }
}

function registerEventHandlers() {
    // TODO: only update events for the modified/added module(s) instead of the entire page (prevent performance issues with large amounts of content)

    $("div.paragraph").each(function () {
        // this paragraph's elements
        $(this).children().each(function () {
            $(this).off("focus keyup keydown mouseup");
            $(this).on("focus", function (e) {
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
                if (key(e, 13)) {
                    e.preventDefault();
                }
                // keyboard shortcuts
                var letter_k = key(e, 75);
                var letter_b = key(e, 66);
                var letter_i = key(e, 73);
                var ctrl = e.ctrlKey;
                if (ctrl) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (letter_k) {
                        shortcut_k()
                    } else if (letter_b) {
                        shortcut_b()
                    } else if (letter_i) {
                        shortcut_i()
                    }
                }
            });
            $(this).on("keyup", function (e) {
                var enter = key(e, 13);
                var backspace = key(e, 8);
                var isMorph = $(this)[0].classList.contains("morph");
                var empty = isElementHTMLEmpty($(this));
                if (backspace) {
                    if (empty && caretHTMLIndex === 0) {
                        // check to make sure this is not the first paragraph/element in the entire page
                        if (currentParagraphIndex === 0 && currentModuleIndex === 0 && currentParagraphElementIndex === 0) {
                            return
                        }
                        // delete this element, knowing that it's not the first paragraph/element in the page
                        var preceding = $(this).parent().children().eq(currentParagraphElementIndex - 1);
                        if (preceding !== null) {
                            $(this).parent().remove();
                            preceding.focus();
                        }
                    } else if (caretHTMLIndex === 0 || caretTextIndex === 0) {
                        // merge with above paragraph or element
                        if (currentParagraphElementIndex === 0) {
                            // backspace-ing in the first element, so merge into the above paragraph
                            mergeParagraph($(this).parent());
                            resetCaretIndexes()
                        } else {
                            // backspace-ing NOT in the first element, so merge into the above element (same paragraph)
                            mergeParagraphElement($(this));
                            resetCaretIndexes()
                        }
                    }
                    return;
                }
                // did not press backspace at beginning of an empty element
                if (isMorph) {
                    $(this).attr("class", strings[9] + " plain");
                    if (enter) {
                        // pressed enter in a morph element, therefore make a whole new paragraph instead of just another element
                        addNewParagraph($(this).parent(), caretTextIndex === $(this)[0].textContent.length)
                    }
                } else if (enter) {
                    // pressed enter in a non-morph element
                    if (selectionLength > 0) {
                        // this actually worked on the first try (Thank you Kayne, very cool!)
                        var prev = $(this).html();
                        var replace = $(this).html().substring(caretHTMLIndex).replace(selectedText, "");
                        $(this).html(prev.substring(0, caretHTMLIndex) + replace)
                    }
                    addNewParagraphElement($(this));
                    resetCaretIndexes()
                }
            });
            $(this).on("mouseup", function () {
                if (isDisplayingLinkHoverer && !finishedShowingLinkHoverer) {
                    setLinkHovererVisible(false, "", false)
                }
                finishedShowingLinkHoverer = false;
            })
        });
    });

    $("div.sub-module.paragraph .e a").off("mouseup");
    $("div.sub-module.paragraph .e a").on("mouseup", function () {
        setLinkHovererVisible(true, $(this).attr("href"), $(this).attr("target") === "_blank");

        $("div.link-hoverer").css("top", $(this).offset().top + 32);
        $("div.link-hoverer").css("left", $(this).offset().left);
        $("div.link-hoverer").trigger("focus");
        finishedShowingLinkHoverer = true;

        currentLinkID = $(this).attr("id")
    });
    $("div.link-hoverer").off("blur");
    $("div.link-hoverer").on("blur", function (e) {
        if (isDisplayingLinkHoverer && !finishedShowingLinkHoverer && !$(e.relatedTarget).parent().parent().hasClass("link-hoverer")) {
            setLinkHovererVisible(false, "", false)
        }
        finishedShowingLinkHoverer = false;
    });

    $("div.module.heading span#removesection").off("click");
    $("div.module.heading span#removesection").on("click", function () {
        var headingModule = $(this).parent();
        // first remove the paragraph container (i.e. section content)
        headingModule.parent().children().eq(headingModule.index() + 1).fadeOut();
        // then remove the actual heading (i.e. section title)
        headingModule.fadeOut()
    });

    $("div.sub-module.list li").off("focus keydown keyup");
    $("div.sub-module.list li").on("focus", function () {
        currentList = $(this).parent();
        currentListIndex = $(this).index();
        currentModule = currentList.parent();
        currentModuleIndex = currentModule.index();
    });
    $("div.sub-module.list li").on("keydown", function (e) {
        if (key(e, 13) && currentList != null && currentListIndex !== -1) {
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
    caretHTMLIndex = 0
}

function setToolbarStatusText(text) {
    $("div.toolbar div.toolbar-status span").html(text);
}

function setToolbarSpinnerVisible(visible) {
    if (visible) {
        $("div.toolbar div.toolbar-spinner").removeClass("hidden");
    } else {
        $("div.toolbar div.toolbar-spinner").addClass("hidden");
    }
}

function setLinkModalVisible(visible) {
    isDisplayingModal = visible;
    if (visible) {
        $("div.link-modal").fadeIn("fast").removeClass("hidden");
    } else {
        $("div.link-modal").fadeOut("fast").addClass("hidden");
    }
}

function setOptionsModalVisible(visible) {
    isDisplayingModal = visible;
    if (visible) {
        setOptionsDialogContent();
        $("div.options-modal").fadeIn("fast").removeClass("hidden");
    } else {
        $("div.options-modal").fadeOut("fast").addClass("hidden");
    }
}

function setLinkHovererVisible(visible, linkURL, newtab) {
    if (visible) {
        setLinkHovererContent(linkURL, newtab);
        if (!isDisplayingLinkHoverer) {
            $("div.link-hoverer").fadeIn("fast");
            $("div.link-hoverer").removeClass("hidden");
            isDisplayingLinkHoverer = true
        }
    } else {
        $("div.link-hoverer").fadeOut(125);
        $("div.link-hoverer").addClass("hidden");
        isDisplayingLinkHoverer = false
    }
}

function setLinkHovererContent(linkURL, newtab) {
    $("div.link-hoverer span > input#linkURL").val(linkURL);
    $("div.link-hoverer span > input#newtab").prop('checked', newtab);
}

function setOptionsDialogContent() {
    $("div.options-dialog div.dialog-content div.textbox-container input#sluginput").val(getHiddenMeta("slug"))
}

function actionButton_disable(classname, iconfile) {
    var ab = $(".actionable.action-" + classname);
    if (ab.hasClass("actionable-disabled")) {
        // already disabled
        return
    }
    var icon = ab.children("span#icon").children("img");
    ab.addClass("actionable-disabled");
    // set icon to its disabled variation
    icon.attr("src", icon.attr("src").replace(iconfile, iconfile + "_disabled"))
}

function actionButton_enable(classname, iconfile) {
    var ab = $(".actionable.action-" + classname);
    if (!ab.hasClass("actionable-disabled")) {
        // already enabled
        return
    }
    var icon = ab.children("span#icon").children("img");
    ab.removeClass("actionable-disabled");
    // set icon to its enabled variation
    icon.attr("src", icon.attr("src").replace(iconfile + "_disabled", iconfile))
}

function getFocusedElement() {
    if (currentModule == null) {
        return null
    }
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
    } else if (currentModule.hasClass("page-title") || currentModule.hasClass("footer")) {
        return null;
    } else {
        return null;
    }
}

function onLinkFocus(elementNode) {
    var link = $(elementNode);
    if (link.attr("id") !== undefined) {
        currentLinkID = link.attr("id")
    } else {
        currentLinkID = ""
    }
}

function removeLinkFrom(html, link) {
    return html.replace(link.prop("outerHTML"), link.html())
}

function removeCurrentLink() {
    currentParagraphElement.html(removeLinkFrom(currentParagraphElement.html(), $("a#" + currentLinkID)))
}

function addNewLink(container) {
    if (container == null) {
        return
    }
    initLinkDialog(container);
}

function addNewParagraph(p, isCarryingContent) {
    if (p == null) {
        return
    }
    var container = p.parent();
    var elementHTML = currentParagraphElement.html();

    currentParagraphElement.remove();
    if (!isCarryingContent) {
        container.insertAt(currentParagraphIndex + 1, strings[0] + strings[10] + elementHTML + "</span></div>")
    } else {
        container.insertAt(currentParagraphIndex + 1, strings[0] + elementHTML + "</div>")
    }
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
        html = removeLinkFrom(html, $("a#" + currentLinkID))
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
        return
    }
    var paragraphAbove = toMerge.parent().children().eq(currentParagraphIndex - 1);
    if (paragraphAbove === null) {
        return
    }
    var aboveHTML = paragraphAbove.html();
    paragraphAbove.html(aboveHTML + toMerge.html());
    toMerge.remove();
    currentParagraph = paragraphAbove;
    currentParagraphIndex = currentParagraph.index();
    currentParagraphElement = currentParagraph.children().eq(currentParagraph.children().length - 1);
    currentParagraphElementIndex = currentParagraphElement.index();
    currentParagraphElement.focus();
    registerEventHandlers()
}

function mergeParagraphElement(toMerge) {
    if (currentParagraphElementIndex === 0) {
        // can't merge into non-existent element (there is not one before it)
        return;
    }
    var elementAbove = toMerge.parent().children().eq(currentParagraphElementIndex - 1);
    if (elementAbove === null) {
        return
    }
    var aboveHTML = elementAbove.html();
    elementAbove.html(aboveHTML + toMerge.html());
    toMerge.remove();
    currentParagraphElement = elementAbove;
    currentParagraphElementIndex = currentParagraphElement.index();
    currentParagraphElement.focus();
    registerEventHandlers()
}

function addNewSection(invoker) {
    if (invoker == null) {
        return;
    }
    var allModules = invoker.parent();
    allModules.insertAt(currentModuleIndex + 1, strings[1]);
    allModules.insertAt(currentModuleIndex + 2, strings[2] + strings[0] + strings[10] + "</span></div></div>");
    allModules.children().eq(currentModuleIndex + 2).focus();
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

function commonSelectionInsert(element, before, after) {
    var html = element.html();
    var replaced = html.substring(caretTextIndex).replace(selectedText, before + selectedText + after);
    element.html(html.substring(0, caretTextIndex) + replaced)
}

function insertLink(element) {
    var url = $("div.link-dialog div.dialog-content div.textbox-container input").val();
    if (!isURLSanitary(url)) {
        alert("Invaild URL! (" + url + ")");
        // TODO: custom error/warning dialog
        // TODO: add option to allow "dirty", or unvalidated, URLs (but have it off by default)
        return
    }
    commonSelectionInsert(element, '<a rel="nofollow noopener noreferrer" target=\"_default\" href="' + url + '" id="' + Date.now().toString() + '">', '</a>')
}

function insertBold(element) {
    commonSelectionInsert(element, '<b>', '</b>')
}

function insertItalics(element) {
    commonSelectionInsert(element, '<i>', '</i>')
}
