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
    // EDIT
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
    $('div.add-new-paragraph button').click(function() {
        var pcontainer = $(this).parent().parent();
        pcontainer.insertAt(pcontainer.children().length - 1, "<div contenteditable=\"true\" class=\"sub-module paragraph\">Here is your new paragraph!</div>")
    });
    $('div.sub-module.paragraph').keyup(function(){
        if ($(this).html().length == 0) {
            $(this).remove()
        }
    })
});