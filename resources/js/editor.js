// https://stackoverflow.com/a/5086688
jQuery.fn.insertAt = function(index, element) {
    var lastIndex = this.children().length;
    if (index < 0) {
        index = Math.max(0, lastIndex + 1 + index);
    }
    this.append(element);
    if (index < lastIndex) {
        this.children().eq(index).before(this.children().last());
    }
    return this;
}

$(document).ready(function() {
    // buttons
    $('div.toolbar div.actionable.action-save').click(function() {
        action_save()
    });
    $('div.toolbar div.actionable.action-back').click(function() {
        action_back()
    });
    $('div.toolbar div.actionable.action-newparagraph').click(function() {
        action_newparagraph()
    });
    $('div.toolbar div.actionable.action-newsection').click(function() {
        action_newsection()
    });
    $('div.toolbar div.actionable.action-newlist').click(function() {
        action_newlist()
    });
    $('div.toolbar div.actionable.action-options').click(function() {
        // window.location = "/editor/?action=delete&id=" + $('span#hiddenpageid').html()
    });
    registerEventHandlers()
});

var strings = [
    "<div class=\"sub-module paragraph\" contenteditable=\"true\">This is a paragraph. Click or tap to change its text.</div>",
    "<div class=\"module heading\"><span contenteditable=\"true\">New Section</span><span id=\"removesection\"><img src=\"/resources/png/trash.png\" alt=\"[X]\" title=\"Remove this section\"></span></div>",
    "<div class=\"module section-content\">",
    "Ready",
    "<div class=\"sub-module list\"><ul>",
    "<li contenteditable=\"true\">List item</li>"
];
var currentModule = null;
var currentModuleIndex = -1;
var currentParagraph = null;
var currentParagraphIndex = -1;
var currentList = null;
var currentListIndex = -1;

function registerEventHandlers() {
    $('div.paragraph').off('keyup');
    $('div.paragraph').off('focus');
    $('div.paragraph').each(function(i) {
        // keyup
        if ($(this).html() != $(this).parent().parent().children().eq(1).children().eq(0).html()) {
            // not the first/intro paragraph, so it can be removed
            $(this).keyup(function(e) {
                if ($(this).html().length == 0 || $(this).html().indexOf('<br') == 0) {
                    $(this).remove()
                }
            })
        }
        // focus
        $(this).focus(function(e) {
            currentModule = $(this).parent();
            currentModuleIndex = currentModule.index();
            currentParagraph = $(this);
            // currentParagraph.addClass("editing-infocus");
            currentParagraphIndex = $(this).index()
        })
    });

    $('div.module.heading span#removesection').off('click');
    $('div.module.heading span#removesection').click(function(e) {
        var headingModule = $(this).parent();
        // first remove the paragraph container
        headingModule.parent().children().eq(headingModule.index() + 1).remove();
        // then remove the actual heading module
        headingModule.remove()
    });

    $('div.sub-module.list li').off('focus');
    $('div.sub-module.list li').off('keydown');
    $('div.sub-module.list li').off('keyup');
    $('div.sub-module.list li').focus(function(e) {
        currentList = $(this).parent();
        currentListIndex = $(this).index();
        currentModule = currentList.parent();
        currentModuleIndex = currentModule.index()
    });
    $('div.sub-module.list li').keydown(function(e) {
        if (event.which == 13 && currentList != null && currentListIndex != -1) {
            // enter or return
            event.preventDefault();
            insertNewListItem()
        }
    });
    $('div.sub-module.list li').keyup(function(e) {
        if ($(this).html().length == 0 || $(this).html().indexOf('<br') == 0) {
            var parent = $(this).parent();
            var amount = parent.children().length;
            // remove list item
            $(this).remove();
            if (amount == 1) {
                // remove list if empty
                parent.remove()
            }
        }
    })
}

function addNewParagraph(invoker) {
    if (invoker == null) {
        return;
    }
    var container = invoker.parent();
    container.insertAt(currentParagraphIndex + 1, strings[0]);
    container.children().eq(currentParagraphIndex + 1).focus();
    // added new paragraph, need to register events for it
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
    currentList.insertAt(currentListIndex + 1, strings[5]);
    currentListIndex++;
    currentList.children().eq(currentListIndex).focus();
    registerEventHandlers()
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

function action_newparagraph() {
    addNewParagraph(currentParagraph)
}

function action_newsection() {
    addNewSection(currentModule)
}

function action_newlist() {
    addNewList(currentParagraph)
}

function action_save() {
    setToolbarSpinnerVisible(true);
    setToolbarStatusText("Saving...");

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

    $('div.module').each(function(i) {
        var classlist = this.classList;
        var mod = {
            type: "",
            value: []
        };
        if (classlist.contains("section-content")) {
            mod.type = "section-content";
            $(this).children(".sub-module").each(function(ii) {
                if (this.classList.contains("paragraph")) {
                    mod.value.push({
                        type: "paragraph",
                        value: [{
                            type: "text",
                            value: $(this).html()
                        }]
                    })
                } else if (this.classList.contains("list")) {
                    var listitems = [];
                    $(this).children().each(function(iii){
                        listitems.push($(this).html())
                    });
                    mod.value.push({
                        type: "list",
                        value: listitems
                    })
                }
            });
        } else if (classlist.contains("heading")) {
            mod.type = "heading";
            mod.value = $(this).children("span").eq(0).html()
        } else {
            mod = ""
        }
        if (mod != "") {
            content.modules.push(mod)
        }
    });

    content.infobox.heading = $('table.infobox tr.heading td div').html();
    content.infobox.image.file = $("table.infobox tr.main-image table tbody tr td img").attr("src");
    content.infobox.image.caption = $("table.infobox tr.main-image table tbody tr td#caption").html();

    $('table.infobox tbody').children().each(function(i) {
        if (this.className == "property") {
            content.infobox.items.push({
                type: "property",
                label: $(this).children("th").html(),
                value: $(this).children("td#value").html()
            })
        } else if (this.className == "sub-heading") {
            content.infobox.items.push({
                type: "sub-heading",
                value: $(this).children("td").children("div").html()
            })
        }
    });

    $.post("/editor/", {
        contentjson: encodeURIComponent(JSON.stringify(content)),
        id: $('span#hiddenpageid').html(),
        isnew: $('span#hiddenpageisnew').html(),
        title: $('div.page-title').html(),
        action: "save"
    }).done(function() {
        setTimeout(function() {
            // timeout to make it look better (too fast!)
            setToolbarSpinnerVisible(false);
            setToolbarStatusText(strings[3])
        }, 1000)
    })
}

function action_back() {
    window.location = "/dashboard/";
}