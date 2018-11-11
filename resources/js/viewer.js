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
    registerEvents()
});

var strings = [
    "<div contenteditable=\"true\" class=\"sub-module paragraph\"></div>",
    "<div class=\"module heading\"><span contenteditable=\"true\">New Section</span><span id=\"removesection\"><img src=\"../resources/png/trash.png\" alt=\"[X]\" title=\"Remove this section\"></span></div>",
    "<div class=\"module paragraph-container\">",
    "Ready",
    "<div class=\"sub-module list\"></div>"
];
var currentParagraphContainer = null;
var currentParagraphIndex = -1;

function registerEvents() {
    $('div.paragraph').off('keyup');
    $('div.paragraph').off('click');
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
        // click
        $(this).click(function(e) {
            currentParagraphContainer = $(this);
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
}

function addNewParagraph(invoker) {
    if (invoker == null) {
        return;
    }
    var container = invoker.parent();
    container.insertAt(currentParagraphIndex + 1, strings[0]);
    container.children().eq(currentParagraphIndex + 1).focus();
    // added new paragraph, need to register events for it
    registerEvents()
}

function addNewSection(invoker) {
    if (invoker == null) {
        return;
    }
    var mod = invoker.parent();
    var modcontainer = mod.parent();
    modcontainer.insertAt(mod.index() + 1, strings[1]);
    modcontainer.insertAt(mod.index() + 2, strings[2] + strings[0] + "</div>");
    modcontainer.children().eq(mod.index() + 1).focus();
    registerEvents()
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
    addNewParagraph(currentParagraphContainer)
}

function action_newsection() {
    addNewSection(currentParagraphContainer)
}

function action_save() {
    setToolbarSpinnerVisible(true);
    setToolbarStatusText("Saving...");

    // save
    var content = {
        modules: [],
        infobox: {
            heading: "Untitled",
            image: {
                file: "",
                caption: "No caption provided"
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
        if (classlist.contains("paragraph-container")) {
            mod.type = "paragraph-container";
            $(this).children(".sub-module").each(function(ii) {
                if (this.classList.contains("paragraph")) {
                    mod.value.push({
                        type: "paragraph",
                        value: [{
                            type: "text",
                            value: $(this).html()
                        }]
                    });
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
            });
        } else if (this.className == "sub-heading") {
            content.infobox.items.push({
                type: "sub-heading",
                value: $(this).children("td").children("div").html()
            });
        }
    });

    $.post("/viewer/", {
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