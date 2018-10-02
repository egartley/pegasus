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

var inFocusParagraph;

function registerEvents() {
    $('div.module.paragraph-container div.paragraph').focusin(function() {
        inFocusParagraph = $(this)
    });
    $('div.paragraph').keyup(function(e) {
        if ($(this).html().length == 0 || $(this).html().indexOf('<br') == 0) {
            $(this).remove()
        }
    });
}

function addNewParagraph() {
    inFocusParagraph.parent().insertAt(inFocusParagraph.parent().children().length, "<div contenteditable=\"true\" class=\"sub-module paragraph\"></div>");
    inFocusParagraph.parent().children().eq(inFocusParagraph.parent().children().length - 1).focus();
    // added new paragraph, need to register events for it
    registerEvents()
}

function enableButton(button) {
    $(button).removeAttr('disabled')
}

function disableButton(button) {
    $(button).prop("disabled", true)
}

$(document).ready(function() {
    // BUTTONS
    $('button.save-changes').click(function() {
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

        $.post("/editor/", { contentjson: encodeURIComponent(JSON.stringify(content)), id: $('span#hiddenpageid').html(), isnew: $('span#hiddenpageisnew').html(), title: $('div.page-title').html(), action: "save" })
    });
    $('button.new-paragraph').click(function() {
        addNewParagraph()
    });

    registerEvents()
});