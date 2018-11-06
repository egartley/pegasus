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
// https://github.com/accursoft/caret
jQuery.fn.caret = function() {
    var target = this[0];
    var isContentEditable = target && target.contentEditable === 'true';
    if (target) {
        if (window.getSelection) {
            if (isContentEditable) {
                target.focus();
                var range1 = window.getSelection().getRangeAt(0),
                    range2 = range1.cloneRange();
                range2.selectNodeContents(target);
                range2.setEnd(range1.endContainer, range1.endOffset);
                return range2.toString().length;
            }
            return target.selectionStart;
        }
        if (target.selectionStart)
            return target.selectionStart;
    }
    return;
}

var strings = [
    "<div class=\"sub-module paragraph\" contenteditable=\"true\"></div>",
    "<div class=\"module heading\" contenteditable=\"true\">New Section</div>",
    "<div class=\"module paragraph-container\">",
    "<div class=\"add-content-container\"><button class=\"add-paragraph\">Add Paragraph</button><br><button class=\"new-section\">New Section</button></div>",
    "Ready"
];

function registerEvents() {
    $('div.paragraph').off('keyup');
    $('div.paragraph').keyup(function(e) {
        if ($(this).html().length == 0 || $(this).html().indexOf('<br') == 0) {
            $(this).remove()
        }
    });
    $('button.add-paragraph').off('click');
    $('button.add-paragraph').click(function(e) {
        addNewParagraph($(e.target))
    });
    $('button.new-section').off('click');
    $('button.new-section').click(function(e) {
        newSection($(e.target))
    });
}

function addNewParagraph(invoker) {
    var container = invoker.parent().parent();
    container.insertAt(container.children().length - 1, strings[0]);
    container.children().eq(container.children().length - 1).focus();
    // added new paragraph, need to register events for it
    registerEvents()
}

function newSection(invoker) {
    var mod = invoker.parent().parent();
    var modcontainer = mod.parent();
    modcontainer.insertAt(mod.index() + 1, strings[1]);
    modcontainer.insertAt(mod.index() + 2, strings[2] + strings[0] + strings[3] + "</div>");
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

$(document).ready(function() {
    // buttons
    $('div.toolbar div.actionable.action99').click(function() {
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
            var mod = { type: "", value: [] };
            if (classlist.contains("paragraph-container")) {
                mod.type = "paragraph-container";
                $(this).children(".sub-module").each(function(ii) {
                    if (this.classList.contains("paragraph")) {
                        mod.value.push({
                            type: "paragraph",
                            value: [{ type: "text", value: $(this).html() }]
                        });
                    }
                });
            } else if (classlist.contains("heading")) {
                mod.type = "heading";
                mod.value = $(this).html();
            } else {
                mod = "";
            }
            if (mod != "") {
                content.modules.push(mod);
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

        $.post("/editor/", { contentjson: encodeURIComponent(JSON.stringify(content)), id: $('span#hiddenpageid').html(), isnew: $('span#hiddenpageisnew').html(), title: $('div.page-title').html(), action: "save" }).done(function(){
            setTimeout(function(){
                // timeout to make it look better (too fast!)
                setToolbarSpinnerVisible(false);
                setToolbarStatusText(strings[4])
            }, 2000)
        })
    });
    registerEvents()
});