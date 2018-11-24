// https://stackoverflow.com/a/5086688
jQuery.fn.insertAt = function (index, element) {
    let lastIndex = this.children().length;
    if (index < 0) {
        index = Math.max(0, lastIndex + 1 + index);
    }
    this.append(element);
    if (index < lastIndex) {
        this.children().eq(index).before(this.children().last());
    }
    return this;
};

let strings = [
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
let currentModule = null;
let currentModuleIndex = 0;
let currentParagraph = null;
let currentParagraphIndex = 0;
let currentList = null;
let currentListIndex = 0;
let currentInfoboxIndex = 0;

// link modal
let linkModalHeight = 32;
let selectionTimeout = 75;
let didFireSelectionEvent = false;
let didSetLeftPosition = false;
let previousSelectionLength = 0;

function editorInit() {
    // TOOLBAR BUTTONS ("actionables")
    $('div.toolbar div.actionable').on("mousedown", function () {
        let t = $(this);
        t.addClass("actionable-clicked");
        setTimeout(function () {
            t.removeClass("actionable-clicked");
        }, 200);
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
        let text = document.getSelection().toString();
        let modal = $("div.link-modal");
        previousSelectionLength = text.length;
        if (text.length === 0) {
            setLinkModalVisible(false);
            return
        }
        if (!didFireSelectionEvent) {
            let r = window.getSelection().getRangeAt(0).getBoundingClientRect();
            let relative = document.body.parentNode.getBoundingClientRect();
            setLinkModalVisible(true);
            // TODO: needs work but ok for now
            modal.css("top", (r.bottom - relative.top - r.height - linkModalHeight) + "px");
            if (!didSetLeftPosition) {
                modal.css("left", r.left + "px");
                didSetLeftPosition = true
            }
            didFireSelectionEvent = true
        } else {
            return
        }
        setTimeout(function () {
            didFireSelectionEvent = false
        }, selectionTimeout)
    };

    // DYNAMIC EVENTS (amount/element can change)
    registerEventHandlers()
}

function action_save() {
    setToolbarSpinnerVisible(true);
    setToolbarStatusText(strings[8]);

    // save
    let content = {
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
        let classlist = this.classList;
        let mod = {
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
                        let listitems = [];
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
            mod = {}
        }
        if (mod !== {}) {
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
        let headingModule = $(this).parent();
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
            let parent = $(this).parent();
            let amount = parent.children().length;
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
        let attr = $(this).attr("contenteditable");
        if (typeof attr !== typeof undefined && attr !== false) {
            $(this).on("click", function (event) {
                console.log(event.delegateTarget);
                checkForLinkModalFix()
            })
        }
    })
}

function isElementHTMLEmpty(element) {
    return element.html().length === 0 || element.html().indexOf('<br') === 0;
}

function setToolbarStatusText(text) {
    $('div.toolbar div.toolbar-status span').html(text)
}

function setToolbarSpinnerVisible(visible) {
    let spin = $('div.toolbar div.toolbar-spinner');
    if (visible) {
        spin.removeClass("hidden")
    } else {
        spin.addClass("hidden")
    }
}

function setLinkModalVisible(visible) {
    if (!visible) {
        $("div.link-modal").css("display", "none");
        didSetLeftPosition = false
    } else {
        $("div.link-modal").css("display", "block")
    }
}

function getHiddenMeta(meta) {
    return $("span#hiddenpage" + meta).html()
}

function checkForLinkModalFix() {
    if (document.getSelection().toString().length === 0 && previousSelectionLength !== 0) {
        setLinkModalVisible(false)
    }
}

function addNewParagraph(invoker) {
    if (invoker == null) {
        return;
    }
    let container = invoker.parent();
    container.insertAt(currentParagraphIndex + 1, strings[0]);
    container.children().eq(currentParagraphIndex + 1).focus();
    registerEventHandlers()
}

function addNewSection(invoker) {
    if (invoker == null) {
        return;
    }
    let allModules = invoker.parent();
    allModules.insertAt(currentModuleIndex + 1, strings[1]);
    allModules.insertAt(currentModuleIndex + 2, strings[2] + strings[0] + "</div>");
    allModules.children().eq(currentModuleIndex + 1).focus();
    registerEventHandlers()
}

function addNewList(invoker) {
    if (invoker == null) {
        return;
    }
    let paragraphContainer = invoker.parent();
    let insertIndex = invoker.index() + 1;
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
