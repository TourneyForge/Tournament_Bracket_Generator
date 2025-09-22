let shufflingPromise = null;

function callShuffle(enableShuffling = true) {
    const delayBetweenRuns = 800; // Delay in milliseconds (0.5 seconds)

    exampleTeams = [];
    
    // Use a promise to coordinate the shuffling and displaying of the message
    shufflingPromise = new Promise(resolve => {
        const startTime = new Date();

        function runFlipFuncSequentially(currentTime) {
            if ((currentTime - startTime) < shuffle_duration * 1000) {
                if (enableShuffling) {
                    setTimeout(function () {
                        shuffleList(() => {
                            runFlipFuncSequentially(new Date());
                        });
                    }, delayBetweenRuns);
                } else {
                    setTimeout(function () {
                        runFlipFuncSequentially(new Date());
                    }, delayBetweenRuns)
                }
            } else {
                // Resolve the promise when all shuffling iterations are complete
                resolve();
            }
        }

        runFlipFuncSequentially(new Date());
    });

    shufflingPromise.then(() => {
        let order = 0
        let notAllowedItems = []
        Array.from(document.getElementById('newList').children).forEach((item, i) => {
            if (item.classList.contains('not-allowed')) {
                notAllowedItems.push(item.dataset.id)
                return
            }
            let img = '';
            let members = [];
            if ($(item).find('img').length > 0) img = $(item).find('img').attr('src');
            if (item.dataset.isGroup) {
                Array.from(item.children[1].children).forEach((member, i) => {
                    members.push({'id': member.dataset.id, 'order': i})
                })
            }
            exampleTeams.push({ 'id': item.dataset.id, 'order': order, 'is_group': item.dataset.isGroup, 'members': members });
            order++
        });

        generateBrackets(exampleTeams, notAllowedItems);
    },
        function (error) { myDisplayer(error); }
    );
}

function skipShuffling() {
    audio.pause();
    document.getElementById('stopAudioButton').textContent = "Resume Audio"
    shuffle_duration = 0;
}

function shuffleList(callback) {
    const list = document.getElementById('newList');

    let children = Array.from(list.children);

    const keys = {}; // Reset keys object for each click

    // Store item elements' id and boundingClientRect
    children.forEach(elm => {
        keys[elm.id] = elm.getBoundingClientRect();
    });

    // Shuffle elements
    children = shuffleArray(Array.from(list.children));
    children.forEach(elm => {
        document.getElementById('newList').appendChild(elm);
    });

    // Apply animations
    Array.from(list.children).forEach(elm => {
        const first = keys[elm.id];
        const last = elm.getBoundingClientRect();

        const delta = {
            x: first.left - last.left,
            y: first.top - last.top,
        };

        gsap.set(elm, { x: delta.x, y: delta.y }); // Set initial position

        gsap.fromTo(elm, {
            x: delta.x,
            y: delta.y,
        }, {
            x: 0,
            y: 0,
            duration: 0.5,
            ease: 'ease-in-out',
            onComplete: function () {
                gsap.set(elm, { clearProps: 'all' }); // Reset properties after animation completes
            }
        });
    });


    // Execute the callback after shuffling
    if (callback && typeof callback === 'function') {
        callback();
    }

}

function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}

/**
 * Render the list of Participants
 */
function renderParticipants(participantsData) {
    itemList.innerHTML = ''
    let enableBtn = document.querySelector('.list-tool-bar .enableBtn').cloneNode(true)

    let participantsArray = participantsData.participants

    if (participantsArray.length) {
        $('.empty-message-wrapper').addClass('d-none')
        document.querySelector('.list-tool-bar').classList.remove('d-none')
        document.querySelector('.list-tool-bar').innerHTML = ''
        document.querySelector('.list-tool-bar').appendChild(enableBtn)
    } else {
        $('.empty-message-wrapper').removeClass('d-none')
        return false
    }

    enable_confirmPopup = true;

    $('.empty-message-wrapper').addClass('d-none')

    let groups = {}

    const noteIcon = document.createElement('button')
    noteIcon.setAttribute('class', "noteBtn ms-2 btn btn-light p-0 bg-transparent border-0")
    noteIcon.innerHTML = `<i class="fa-classic fa-solid fa-circle-exclamation"></i>`
    noteIcon.setAttribute('data-bs-toggle', 'tooltip');
    noteIcon.setAttribute('data-bs-placement', 'top');
    noteIcon.setAttribute('data-bs-html', true)
    noteIcon.setAttribute('title', 'You may group individual participants together by selecting each one in the list belonging to the same group.<br/>Note: Nested grouping is not an option, meaning groups cannot be grouped within one another!');
    const tooltip = new bootstrap.Tooltip(noteIcon)
    document.querySelector('.list-tool-bar').appendChild(noteIcon)
    
    participantsArray.forEach((participant, i) => {
        if (parseInt(participant.is_group)) {
            return
        }

        var item = document.createElement('div');
        item.setAttribute('id', participant.id);
        item.setAttribute('class', "participant list-group-item d-flex");
        item.setAttribute('data-id', participant.id);
        item.setAttribute('data-name', participant.name);

        let itemInfo = '';
        if (participant.invitation_disabled) {
            item.classList.add('not-allowed')
            itemInfo = `<button class="btn btn-light bg-transparent border-0 p-0 ms-3" data-bs-toggle="tooltip" data-bs-title="This participant declined invitations to tournaments, therefore they won't be included in the brackets."><i class="fa-classic fa-solid fa-circle-exclamation"></i></button>`
        }

        let item_html = `<span class="p-name ms-3">` + participant.name + '</span>' + itemInfo;
        if(participant.image) {
            item_html = `<div class="p-image"><img src="${participant.image}" class="col-auto" height="30px" id="pimage_${participant.id}" data-pid="${participant.id}"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${participant.id})" name="image_${participant.id}" id="image_${participant.id}"/><button class="btn btn-danger d-none col-auto" onClick="removeImage(event, ${participant.id})"><i class="fa fa-trash-alt"></i></button></div>` + item_html;
        }else{
            item_html = `<div class="p-image"><img src="/images/avatar.jpg" class="temp col-auto" id="pimage_${participant.id}" data-pid="${participant.id}" height="30px"/><input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this, ${participant.id})" name="image_${participant.id}" id="image_${participant.id}"/><button class="btn btn-danger d-none col-auto" onClick="removeImage(event, ${participant.id})"><i class="fa fa-trash-alt"></i></button></div>` + item_html;
        }
        item_html += '<button class="btn btn-light bg-transparent ms-auto border-0 p-0" data-bs-toggle="tooltip" data-bs-title="Individual Participant"><svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <circle cx="8" cy="6" r="3.25"></circle> <path d="m2.75 14.25c0-2.5 2-5 5.25-5s5.25 2.5 5.25 5"></path> </g></svg></button>'

        item.innerHTML = item_html;

        if (!participant.g_id) {
            itemList.appendChild(item)
        } else {
            if (!(participant.g_id in groups)) {
                const groupHtml = document.createElement('div')
                groupHtml.setAttribute('class', 'group')
                groupHtml.setAttribute('data-id', participant.g_id)
                groupHtml.setAttribute('data-is-group', 1)

                let hasImgClass = (participant.group_image) ? "has-img" : '';
                if (!hasImgClass) participant.group_image = "/images/group-placeholder.png"

                const groupLabel = document.createElement('div')
                groupLabel.setAttribute('class', "group-name list-group-item d-flex align-items-center ps-3 border-bottom")
                groupLabel.innerHTML = `<img src="${participant.group_image}" class="group-image ${hasImgClass} pe-2"><span class="name me-auto">${participant.group_name}</span>`
                groupLabel.innerHTML += '<button class="btn btn-light bg-transparent ms-auto border-0 p-0" data-bs-toggle="tooltip" data-bs-title="Group"><svg fill="#000000" height="200px" width="200px" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 24 24" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g id="group"> <path d="M24,15.9c0-2.8-1.5-5-3.7-6.1C21.3,8.8,22,7.5,22,6c0-2.8-2.2-5-5-5c-2.1,0-3.8,1.2-4.6,3c0,0,0,0,0,0c-0.1,0-0.3,0-0.4,0 c-0.1,0-0.3,0-0.4,0c0,0,0,0,0,0C10.8,2.2,9.1,1,7,1C4.2,1,2,3.2,2,6c0,1.5,0.7,2.8,1.7,3.8C1.5,10.9,0,13.2,0,15.9V20h5v3h14v-3h5 V15.9z M17,3c1.7,0,3,1.3,3,3c0,1.6-1.3,3-3,3c0-1.9-1.1-3.5-2.7-4.4c0,0,0,0,0,0C14.8,3.6,15.8,3,17,3z M13.4,4.2 C13.4,4.2,13.4,4.2,13.4,4.2C13.4,4.2,13.4,4.2,13.4,4.2z M15,9c0,1.7-1.3,3-3,3s-3-1.3-3-3s1.3-3,3-3S15,7.3,15,9z M10.6,4.2 C10.6,4.2,10.6,4.2,10.6,4.2C10.6,4.2,10.6,4.2,10.6,4.2z M7,3c1.2,0,2.2,0.6,2.7,1.6C8.1,5.5,7,7.1,7,9C5.3,9,4,7.7,4,6S5.3,3,7,3 z M5.1,18H2v-2.1C2,13.1,4.1,11,7,11v0c0,0,0,0,0,0c0.1,0,0.2,0,0.3,0c0,0,0,0,0,0c0.3,0.7,0.8,1.3,1.3,1.8 C6.7,13.8,5.4,15.7,5.1,18z M17,21H7v-2.1c0-2.8,2.2-4.9,5-4.9c2.9,0,5,2.1,5,4.9V21z M22,18h-3.1c-0.3-2.3-1.7-4.2-3.7-5.2 c0.6-0.5,1-1.1,1.3-1.8c0.1,0,0.2,0,0.4,0v0c2.9,0,5,2.1,5,4.9V18z"></path> </g> </g></svg></button>'

                groupLabel.setAttribute('data-bs-toggle', "collapse")
                groupLabel.setAttribute('data-bs-target', `#group_${participant.g_id}`)
                groupLabel.setAttribute('data-name', `${participant.group_name}`)

                const groupList = document.createElement('div')
                groupList.setAttribute('id', `group_${participant.g_id}`)
                groupList.setAttribute('class', 'list-group list-group-numbered ms-3 collapse')
                groupList.setAttribute('data-group', participant.g_id)
                groupList.setAttribute('data-name', participant.group_name)

                groupHtml.appendChild(groupLabel)
                groupHtml.appendChild(groupList)

                groups[participant.g_id] = groupHtml

                itemList.appendChild(groups[participant.g_id])
            }

            groups[participant.g_id].children[1].appendChild(item);
        }
    });

    $.contextMenu('destroy');

    $('#newList').contextMenu({
        selector: '.group-name',
        build: function ($triggerElement, e) {
            let items = {}
            let editItemLabel = "Edit Group"
            let deleteItemLabel = "Delete Group"
            const reused = participantsData.reusedGroups.includes($triggerElement.parent().data('id'))

            if (reused) {
                editItemLabel += `<span data-toggle='tooltip' title="Reused groups cannot be edited due to their associations with other tournaments."><i class="fa-classic fa-solid fa-circle-exclamation"></i></span>`
                deleteItemLabel += `<span data-toggle='tooltip' title="Reused groups cannot be deleted due to their associations with other tournaments."><i class="fa-classic fa-solid fa-circle-exclamation"></i></span>`
            }

            items.edit = {
                name: editItemLabel,
                isHtmlName: true,
                disabled: reused,
                callback: (key, opt, e) => {
                    enableGroupEdit(opt.$trigger)
                }
            }
            items.delete = {
                name: deleteItemLabel,
                isHtmlName: true,
                disabled: reused,
                callback: (key, opt, e) => {
                    deleteGroup(opt.$trigger)
                }
            }
            items.ungroup = {
                name: "Ungroup",
                callback: (key, opt, e) => {
                    ungroup(opt.$trigger)
                }
            }

            return {
                html: true,
                items: items,
                events: {
                    show: function(opt) {
                        // Initialize tooltips when menu is shown
                        $('.context-menu-list [data-toggle="tooltip"]').tooltip({
                            trigger: 'hover',
                            placement: 'right',
                            container: 'body'
                        });
                    },
                    hide: function(opt) {
                        // Destroy tooltips when menu hides to prevent memory leaks
                        $('.context-menu-list [data-toggle="tooltip"]').tooltip('dispose');
                    }
                }
            }
        }
    })

    $('#newList').contextMenu({
        selector: '.participant',
        build: function ($triggerElement, e) {
            let items = {}
            items.edit = {
                name: "Edit",
                callback: (key, opt, e) => {
                    originalHtml = opt.$trigger.html()

                    var element_id = opt.$trigger.data('id');
                    const nameBox = document.createElement('input');
                    const name = opt.$trigger.find('.p-name').html().trim();
                    nameBox.classList.add('name-input', 'form-control');
                    nameBox.value = name;

                    $(nameBox).atwho({
                        at: "@",
                        searchKey: 'username',
                        data: users,
                        limit: 5, // Show only 5 suggestions
                        displayTpl: "<li data-value='@${id}'>${username}</li>",
                        insertTpl: "@${username}",
                        callbacks: {
                            remoteFilter: function (query, callback) {
                                if (query.length < 1) return; // Don't fetch on empty query
                                $.ajax({
                                    url: apiURL + '/tournaments/get-users', // Your API endpoint
                                    type: "GET",
                                    data: {
                                        query: query
                                    },
                                    dataType: "json",
                                    success: function (data) {
                                        callback(data);
                                    }
                                });
                            }
                        }
                    });

                    const inputBox = document.createElement('div');
                    inputBox.appendChild(nameBox);
                    inputBox.classList.add('col');

                    const buttonBox = document.createElement('div');
                    const button = document.createElement('button');
                    button.classList.add('btn', 'btn-primary');
                    button.textContent = "Save";
                    button.setAttribute('onClick', `saveParticipant(event, ${element_id})`);
                    buttonBox.appendChild(button);
                    buttonBox.classList.add('col-auto');

                    const cancelBtn = document.createElement('button')
                    cancelBtn.classList.add('btn', 'btn-secondary', 'ms-2')
                    cancelBtn.textContent = 'Cancel'
                    cancelBtn.addEventListener('click', (event) => {
                        $(event.target).parents('.list-group-item').html(originalHtml)
                    })
                    buttonBox.appendChild(cancelBtn)

                    const html = document.createElement('div');
                    //html.innerHTML = `<input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onChange="checkBig(this)" name="image_${element_id}" id="image_${element_id}"/><button class="btn btn-success col-auto" onClick="chooseImage(event, ${element_id})"><i class="fa fa-upload"></i></button>`;
                    html.appendChild(inputBox);
                    html.appendChild(buttonBox);
                    html.classList.add('row', 'g-3', 'align-items-center');

                    opt.$trigger.html(html);
                }
            }
            items.delete = {
                name: "Delete",
                callback: (key, opt, e) => {
                    var element = opt.$trigger
                    var element_id = opt.$trigger.data('id');
                    $.ajax({
                        type: "POST",
                        url: apiURL + '/participants/delete/' + element_id,
                        data: {tournament_id: tournament_id, hash: hash},
                        success: function (result) {
                            if (result.status == 'success') {
                                let group_id
                                
                                if (element.parents('.group')) {
                                    group_id = element.parents('.group').data('id')
                                }

                                renderParticipants(result)

                                if (group_id) 
                                    collapseOutGroup(group_id, 'g')
                            }
                        },
                        error: function (error) {
                            console.log(error);
                        }
                    }).done(() => {
                        setTimeout(function () {
                            $("#overlay").fadeOut(300);
                        }, 500);
                    });
                }
            }

            if ($triggerElement.parent().data('group')) {
                items.ungroup = {
                    name: `Remove from Group "${$triggerElement.parent().data('name')}"`,
                    callback: (key, opt, e) => {
                        removeParticipantFromGroup(opt.$trigger)
                    }
                }
            }

            return {
                items: items
            }
        }
    });
    
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    cancelMakeGroup()
}

/**
 * Initialize the list of Participants
 */
function loadParticipants() {
    $("#overlay").fadeIn(300);
    
    if (!tournament_id) {
        renderParticipants([]);
        return false;
    }

    $.ajax({
        type: "GET",
        url: apiURL + '/tournaments/' + tournament_id + '/get-participants',
        dataType: "JSON",
        success: function (result) {
            renderParticipants(result);
        },
        error: function (error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function () {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}

$(document).on("click", ".p-image img", function(){
    var pid = $(this).data('pid');
    if($(this).hasClass('temp')){
        $("#image_" + pid).trigger('click');
    }else{
        $(this).parent().addClass('active');
        $(this).parent().find('button').removeClass('d-none')
    }
})

$(document).on("click", "#group_image", function(){
    if($(this).hasClass('temp')){
        $("#group_image_input").trigger('click');
    } else {
        $("#group_image" + ' ~ .btn').removeClass('d-none');
    }
})

$(document).on("click", function(e) {
    if (!$(e.target.parentElement).hasClass('p-image')) {
        $(".p-image").removeClass('active')
        $(".p-image button").addClass('d-none')
    };
})

function chooseImage(e, element_id){
    $("#image_" + element_id).trigger('click');
}
function checkBig(el, element_id){
    var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    const fileInput = el;

    if (!allowedTypes.includes(el.files[0].type)) {
        $('#errorModal .errorDetails').html('Please upload image as *.jpeg, *.jpg, *.png, *.gif format.')
        $("#errorModal").modal('show');

        this.value = '';
        return
    }

    if (el.files[0].size > 3145728) {
        $('#errorModal .errorDetails').html('Max image size is 3MB. Please upload small image.')
        $("#errorModal").modal('show');
        
        this.value='';
        return
    }else{
        var formData = new FormData();
        formData.append('image', $("#image_" + element_id)[0].files[0]);
        formData.append('tournament_id', tournament_id)
        formData.append('hash', hash)

        $.ajax({
            type: "POST",
            url: apiURL + '/participants/update/' + element_id,
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function (result) {
                if (result.errors) {
                    $('#errorModal .errorDetails').html(result.errors.file)
                    $("#errorModal").modal('show');

                    return false
                }

                let group_id
                if ($(fileInput).parents('.group')) 
                    group_id = $(fileInput).parents('.group').data('id')

                renderParticipants(result)

                if (group_id)
                    collapseOutGroup(group_id, 'g')
            },
            error: function (error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function () {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    }
}

function removeImage(e, element_id){
    $.ajax({
        type: "POST",
        url: apiURL + '/participants/update/' + element_id,
        data: {'action': 'removeImage', 'tournament_id': tournament_id, 'hash': hash},
        success: function (result) {
            let group_id
            if ($(e.target).parents('.group')) 
                group_id = $(e.target).parents('.group').data('id')

            renderParticipants(result)

            if (group_id)
                collapseOutGroup(group_id, 'g')
        },
        error: function (error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function () {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}

function saveParticipant(e, element_id) {
    const name = $(e.target).parents('.list-group-item').find('.name-input').val().trim();

    if (!name) {
        $('#errorModal .errorDetails').html(`Participant name cannot be blank!`)
        $('#errorModal').modal('show')

        isValidate = false

        return false
    }

    let parentElement = $(e.target).parent().parent().parent()

    if (parentElement.data('name') == name) {
        confirm(" No changes were made")
        return false
    }

    let ability = true;
    $('.p-name').each((i, e) => {
        if (e.textContent.trim().toLowerCase() == name.toLowerCase()) {
            let confirm_result = confirm("The same name already exists in the list. Are you sure you want to proceed?");

            if (confirm_result == false) {
                ability = false;
                return false;
            }
        }
    });

    if (ability) {
        var formData = new FormData();
        formData.append('name', name);
        formData.append('tournament_id', tournament_id)
        formData.append('hash', hash)
        // formData.append('image', $("#image_" + element_id)[0].files[0]);
        $.ajax({
            type: "POST",
            url: apiURL + '/participants/update/' + element_id,
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function (result) {
                if (result.result !== "success") {
                    $('#errorModal .errorDetails').html(result.message)
                    $('#errorModal').modal('show')

                    let name = $(e.target).parents('.participant').data('name')
                    $(e.target).parents('.participant').find('.name-input').val(name)

                    return false
                }

                let group_id
                if ($(e.target).parents('.group')) 
                    group_id = $(e.target).parents('.group').data('id')

                renderParticipants(result)

                if (group_id)
                    collapseOutGroup(group_id, 'g')
            },
            error: function (error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function () {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    }
}

function generateBrackets(list, notAllowedItems = null) {
    $.ajax({
        type: "post",
        url: apiURL + '/brackets/generate',
        data: { 'type': eleminationType, 'tournament_id': tournament.id, 'user_id': user_id, 'list': list, 'notAllowedList': notAllowedItems, 'hash': hash },
        beforeSend: function() {
            $('#generateProcessing').removeClass('d-none')
        },
        success: function (result) {
            if (result.result == 'success') {
                window.location.href = '/tournaments/' + tournament.id + '/view' 
            } else {
                $('#errorModal .errorDetails').html(result.message)
                $("#errorModal").modal('show');

                return false
            }
        },
        error: function (error) {
            console.log(error);
        }
    }).done(() => {
        $('#generateProcessing').addClass('d-none')
        setTimeout(function () {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}

var addParticipants = (data) => {
    $.ajax({
        type: "POST",
        url: apiURL + '/participants/new',
        data: {
            'name': data.names,
            'user_id' : data.user_id,
            'tournament_id': data.tournament_id,
            'hash': hash
        },
        dataType: "JSON",
        beforeSend: function() {
            $('#beforeProcessing').removeClass('d-none')
        },
        success: function(result) {
            $('#beforeProcessing').addClass('d-none')
            if (result.count) {
                renderParticipants(result);

                $('#participantNames').val(null);
                $('input.csv-import').val(null)
                $('#confirmSave').modal('hide');
                $('#collapseAddParticipant').removeClass('show');

                appendAlert('Records inserted successfully!', 'success');
            }

            if (result.notAllowedParticipants.length) {
                let names = ''
                result.notAllowedParticipants.forEach((participant, i) => {
                    names += participant
                    if (i < (result.notAllowedParticipants.length - 1)) {
                        names += ', '
                    }
                })
                let html = `<h5>The following participant(s) declined invitations to tournaments.<h5>
                    <span class="text-danger">${names}</span><br/><br/>
                    <h5>Therefore, the invitation will be voided.</h5>`
                
                $('#errorModal .errorDetails').html(html);
                
                $("#errorModal").modal('show');
            }

            $('#collapseAddParticipant').removeClass('show');
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}

var validateParticipantNames = (names) => {
    let exisingNames = []
    document.querySelectorAll('#newList .p-name').forEach((item, i) => {
        exisingNames.push(item.textContent.trim())
    })

    let validNames = []
    let duplicates = []
    names.forEach(name => {
        const normalizedValue = name.replace(/\s+/g, '').toLowerCase();
        if (exisingNames.some(element => element.replace(/\s+/g, '').toLowerCase() === normalizedValue) || validNames.some(element => element.replace(/\s+/g, '').toLowerCase() === normalizedValue)) {
            duplicates.push(name)
        } else {
            validNames.push(name)
        }
    })

    return {'duplicates': duplicates, 'validNames': validNames}
}

var checkDuplicatedParticipants = () => {
    var items = $('#newList span.p-name')
    const names = _.map(items, (ele) => {
        return {
            'id': ele.parent.dataset.id,
            'name': ele.textContent
        }
    })

    if (!names.length) {
        return false;
    }

    let duplications = _.chain(names).groupBy('name').filter(function(v) {
        return v.length > 1
    }).flatten().uniq().value()

    if (duplications.length) {
        duplications = _.map(_.uniq(duplications, function(item) {
            return item.name;
        }), function(item) {
            return item.name
        })

        return duplications
    } else {
        return false
    }
}

$(document).ready(function () {
    $(".audio-setting .time").inputmask(
        "99:59:59",
        {
            placeholder: "00:00:00",
            insertMode: false,
            showMaskOnHover: false,
            definitions: {
                '5': {
                    validator: "[0-5]",
                    cardinality: 1
                }
            }
        });
});

let enableGroupParticipants = () => {
    document.querySelectorAll('#newList > .list-group-item').forEach(element => {
        if (element.classList.contains('not-allowed')) {
            return
        }
        
        // Add checkboxs to the participant list
        const checkBoxWrapper = document.createElement('div')
        checkBoxWrapper.setAttribute('class', 'form-check')

        const checkBox = document.createElement('input')
        checkBox.setAttribute('type', "checkbox")
        checkBox.setAttribute('class', "form-check-input")
        checkBox.setAttribute('value', element.id)

        checkBoxWrapper.appendChild(checkBox)
        element.prepend(checkBoxWrapper)

        checkBox.addEventListener('change', event => {
            if (!event.target.checked) {
                document.getElementById('selectAll').checked = false
            }
        })
    })

    const selectAllEl = document.createElement('div')
    selectAllEl.setAttribute('class', 'selectAll d-flex align-items-center form-check form-check-inline')
    selectAllEl.innerHTML = `
        <input class="form-check-input me-2" type="checkbox" id="selectAll">
        <label class="form-check-label" for="selectAll">Select All</label>`
    selectAllEl.querySelector('#selectAll').addEventListener('change', event => {
        document.querySelectorAll('#newList > .list-group-item input[type="checkbox"]').forEach(element => {
            element.checked = event.target.checked ? true : false
        })
    })

    // Add the Make a Group button
    const makeGroupBtn = document.createElement('button')
    makeGroupBtn.setAttribute('class', "group-action btn btn-primary ms-auto")
    makeGroupBtn.textContent = "Save"

    const cancelBtn = document.createElement('button')
    cancelBtn.setAttribute('class', "group-action btn btn-secondary ms-2")
    cancelBtn.textContent = "Cancel"

    makeGroupBtn.addEventListener('click', makeGroup)
    cancelBtn.addEventListener('click', cancelMakeGroup)

    document.querySelector('.list-tool-bar .enableBtn').classList.add('d-none')
    document.querySelector('.list-tool-bar .noteBtn').classList.add('d-none')
    document.querySelector('.list-tool-bar').appendChild(makeGroupBtn)
    document.querySelector('.list-tool-bar').appendChild(cancelBtn)
    document.querySelector('.list-tool-bar').prepend(selectAllEl)
}

let makeGroup = (event) => {
    group_participants = []
    document.querySelectorAll('#newList > .list-group-item input[type="checkbox"]').forEach(element => {
        if (element.checked) {
            group_participants.push(element.value)
        }
    })

    if (group_participants.length) {
        $('#makeGroupModal').modal('show')
    } else {
        $('#selectParticipantsAlertModal').modal('show')
    }
}

let cancelMakeGroup = (event) => {
    const checkboxs = document.querySelectorAll('#newList > .list-group-item input[type="checkbox"]');
    checkboxs.forEach(ckb => ckb.parentElement.remove());

    const buttons = document.querySelectorAll('.list-tool-bar .btn.group-action');
    buttons.forEach(btn => btn.remove());

    if (document.querySelector('.list-tool-bar .selectAll'))
        document.querySelector('.list-tool-bar .selectAll').remove()

    if (document.querySelector('.list-tool-bar .enableBtn'))
        document.querySelector('.list-tool-bar .enableBtn').classList.remove('d-none')
    if (document.querySelector('.list-tool-bar .noteBtn'))
        document.querySelector('.list-tool-bar .noteBtn').classList.remove('d-none')
}

let drawGroupsInModal = () => {
    $.ajax({
        url: apiURL + '/groups/get-list',
        type: "get",
        beforeSend: function() {
            $('#beforeProcessing').removeClass('d-none')
            $("#err").fadeOut();
        },
        success: function(result) {
            $('#beforeProcessing').addClass('d-none')
            if (result.status == 'success' && result.groups.length) {
                document.querySelector('#makeGroupModal #select_group select').innerHTML = ''
                result.groups.forEach(group => {
                    let option = document.createElement('option')
                    option.setAttribute('value', group.id)
                    option.setAttribute('data-image', group.image_path ?? '')
                    option.textContent = group.group_name
                    document.querySelector('#makeGroupModal #select_group select').appendChild(option)
                })

                chooseGroupType(document.querySelector('#makeGroupModal #create_new_group'))
            }
        },
        error: function(e) {
            $("#err").html(e).fadeIn();
        }
    });
}

let chooseGroupType = (element) => {
    if (element.value == 'new') {
        document.querySelector('#makeGroupModal #input_group_name').classList.remove('d-none')
        document.querySelector('#makeGroupModal #input_group_name input').removeAttribute('disabled')
        document.querySelector('#makeGroupModal #select_group').classList.add('d-none')
        document.querySelector('#makeGroupModal #select_group select').setAttribute('disabled', true)
        document.querySelector('#makeGroupModal .group-image img').setAttribute('src', '/images/group-placeholder.png')

        if (document.querySelector('#makeGroupModal .group_image_delete')) {
            document.querySelector('#makeGroupModal .group_image_delete').remove()
        }
    }

    if (element.value == 'reuse') {
        document.querySelector('#makeGroupModal #input_group_name').classList.add('d-none')
        document.querySelector('#makeGroupModal #input_group_name input').value = null
        document.querySelector('#makeGroupModal #input_group_name input').setAttribute('disabled', true)
        document.querySelector('#makeGroupModal #select_group').classList.remove('d-none')
        document.querySelector('#makeGroupModal #select_group select').removeAttribute('disabled')
        let selectedOption = document.querySelector('#makeGroupModal #select_group select').firstChild
        selectedOption.selected = true
        if (selectedOption.getAttribute('data-image')) {
            document.querySelector('#makeGroupModal .group-image img').setAttribute('src', selectedOption.getAttribute('data-image'))

            if (!document.querySelector('#makeGroupModal .group_image_delete')) {
                const deleteImgBtn = document.createElement('button')
                deleteImgBtn.setAttribute('class', "group_image_delete btn")
                deleteImgBtn.innerHTML = `<i class="fa fa-close"></i>`
                deleteImgBtn.addEventListener('click', event => {
                    removeGroupImage(event)
                })
                document.querySelector('#makeGroupModal .group-image').appendChild(deleteImgBtn)
            }
        } else {
            document.querySelector('#makeGroupModal .group-image img').setAttribute('src', '/images/group-placeholder.png')
            if (document.querySelector('#makeGroupModal .group_image_delete')) {
                document.querySelector('#makeGroupModal .group_image_delete').remove()
            }
        }
    }
}

let saveGroup = (e, forceInsert = false) => {
    e.preventDefault()
    
    let isValidate = true

    $('#errorModal .modal-footer button.force').remove()

    if (!document.querySelector('#input_group_name input').value && document.querySelector('#select_group select').getAttribute('disabled')) {
        document.querySelector('#errorModal .errorDetails').innerHTML = 'Please enter a Group Name or select an existing group'
        $('#errorModal').modal('show')

        return false
    }

    if (!forceInsert) {
        [...document.querySelectorAll('#select_group option'), ...document.querySelectorAll('#newList .p-name')].forEach(optionEl => {
            if (!isValidate) {
                return false
            }

            if (document.querySelector('#input_group_name input').value.toLowerCase() == optionEl.textContent.toLowerCase()) {
                const includeBtn = document.createElement('button')
                includeBtn.setAttribute('class', "btn btn-primary force")
                includeBtn.textContent = "Save duplicated name"
                includeBtn.addEventListener('click', () => {
                    saveGroup(e, true)
                })
                $('#errorModal .modal-footer').prepend(includeBtn)
                $('#errorModal .errorDetails').html(`The group name "${document.querySelector('#input_group_name input').value}" appears to be duplicated.`)
                $('#errorModal').modal('show')

                isValidate = false

                return false
            }
        })

        if (!isValidate) {
            return false
        }
    }
    
    if (forceInsert) {
        $('#errorModal').modal('hide')
    }

    const data = Object.fromEntries($('#create_group_form').serializeArray().map(({
        name,
        value
    }) => [name, value]));

    data['hash'] = hash

    if (group_participants.length) {
        data['participants'] = group_participants
    } else {
        return false
    }

    if (tournament) {
        data['tournament_id'] = tournament.id
    }

    $.ajax({
        url: apiURL + '/groups/save',
        type: "POST",
        data: data,
        beforeSend: function () {
            //$("#preview").fadeOut();
            $('#create_group_form').modal('hide');
            $('#beforeProcessing').removeClass('d-none')
            $("#err").fadeOut();
        },
        success: function (result) {
            if (result.status == 'success') {
                $('#makeGroupModal').modal('hide')
                renderParticipants(result)
            }
        },
        error: function (e) {
            $("#err").html(e).fadeIn();
        }
    }).done(() => {
        setTimeout(function () {
            $("#beforeProcessing").fadeOut(300);
        }, 500)
    });
}

let updateGroup = (e, forceUpdate = false) => {
    e.preventDefault()
    
    let isValidate = true

    $('#errorModal .modal-footer button.force').remove()

    if (!forceUpdate) {
        if (document.querySelector('.new-group-name').value == '') {
            $('#errorModal .errorDetails').html(`Group name cannot be blank!`)
            $('#errorModal').modal('show')

            isValidate = false

            return false
        }

        [...document.querySelectorAll('#newList .name'), ...document.querySelectorAll('#newList .p-name')].forEach(optionEl => {
            if (!isValidate) {
                return false
            }

            if (document.querySelector('.new-group-name').value.toLowerCase() == optionEl.textContent.toLocaleLowerCase()) {
                const includeBtn = document.createElement('button')
                includeBtn.setAttribute('class', "btn btn-primary force")
                includeBtn.textContent = "Save duplicated name"
                includeBtn.addEventListener('click', () => {
                    updateGroup(e, true)
                })
                $('#errorModal .modal-footer').prepend(includeBtn)
                $('#errorModal .errorDetails').html(`The group name "${document.querySelector('.new-group-name').value}" appears to be duplicated.`)
                $('#errorModal').modal('show')

                isValidate = false

                return false
            }
        })

        if (!isValidate) {
            return false
        }
    }
    
    if (forceUpdate) {
        $('#errorModal').modal('hide')
    }

    const data = {'group_id': e.target.parentElement.parentElement.parentElement.dataset.id, 'group_name': document.querySelector('.new-group-name').value, 'image_path': e.target.parentElement.parentElement.querySelector('.group-image').getAttribute('src'), 'hash': hash}

    if (tournament) {
        data['tournament_id'] = tournament.id
    } else {
        data['tournament_id'] = 0
    }

    $.ajax({
        url: apiURL + '/groups/save',
        type: "POST",
        data: data,
        beforeSend: function () {
            $('#beforeProcessing').removeClass('d-none')
            $("#err").fadeOut();
        },
        success: function (result) {
            if (result.status == 'success') {
                let group_id
                if ($(e.target).parents('.group')) 
                    group_id = $(e.target).parents('.group').data('id')

                renderParticipants(result)

                if (group_id)
                    collapseOutGroup(group_id, 'g')
            }
        },
        error: function (e) {
            $("#err").html(e).fadeIn();
        }
    }).done(() => {
        setTimeout(function () {
            $("#beforeProcessing").fadeOut(300);
        }, 500)
    });
}

let uploadGroupImage = (el) => {
    var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (!allowedTypes.includes(el.files[0].type)) {
        $('#errorModal .errorDetails').html('Please upload image as *.jpeg, *.jpg, *.png, *.gif format.')
        $("#errorModal").modal('show');

        this.value = '';
        return
    }

    if (el.files[0].size > 3145728) {
        $('#errorModal .errorDetails').html('Max image size is 3MB. Please upload small image.')
        $("#errorModal").modal('show');
        
        this.value='';
        return
    }else{
        var formData = new FormData();
        formData.append('image', el.files[0]);
        formData.append('type', "group");

        $.ajax({
            type: "POST",
            url: apiURL + '/upload-image',
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function (result) {
                if (result.status == 'error') {
                    $('#errorModal .errorDetails').html(result.errors.file)
                    $("#errorModal").modal('show');

                    return false
                }

                el.parentElement.querySelector('img').src = result.file_path
                if (el.parentElement.querySelector('input#group_image_path')) {
                    el.parentElement.querySelector('input#group_image_path').value = result.file_path
                }
                el.parentElement.querySelector('img').classList.remove('temp');

                const deleteImgBtn = document.createElement('button')
                deleteImgBtn.setAttribute('class', "group_image_delete btn")
                deleteImgBtn.innerHTML = `<i class="fa fa-close"></i>`
                deleteImgBtn.addEventListener('click', event => {
                    removeGroupImage(event)
                })
                el.parentElement.appendChild(deleteImgBtn)
            },
            error: function (error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function () {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    }
}

let removeGroupImage = (event) => {
    let parentEl = event.target.parentElement.parentElement
    parentEl.querySelector('img').src = '/images/group-placeholder.png'
    parentEl.querySelector('img').classList.add('temp')

    if (parentEl.querySelector('#group_image_input'))
        parentEl.querySelector('#group_image_input').value = ''
    if (parentEl.querySelector('.group_image_delete'))
        parentEl.querySelector('.group_image_delete').remove()
}

let selectGroup = (el) => {
    const selectedOption = el.options[el.selectedIndex];
    if (selectedOption.dataset.image && selectedOption.dataset.image != 'null') {
        document.getElementById('group_image').src = selectedOption.dataset.image
        document.getElementById('group_image_path').value = selectedOption.dataset.image

        if (!document.querySelector('#makeGroupModal .group_image_delete')) {
            const deleteImgBtn = document.createElement('button')
            deleteImgBtn.setAttribute('class', "group_image_delete btn")
            deleteImgBtn.innerHTML = `<i class="fa fa-close"></i>`
            deleteImgBtn.addEventListener('click', event => {
                removeGroupImage(event)
            })
            document.querySelector('#makeGroupModal .group-image').appendChild(deleteImgBtn)
        }
    } else {
        document.getElementById('group_image').src = '/images/group-placeholder.png'
        document.getElementById('group_image_path').value = null
        if (document.querySelector('#makeGroupModal .group_image_delete')) {
            document.querySelector('#makeGroupModal .group_image_delete').remove()
        }
    }
}

let ungroup = (el) => {
    let group_id = el.parent().data('id')
    $('#confirmModal .message').html(`Are you sure to ungroup the participants from the group "${el.data('name')}"?`)
    $('#confirmModal').modal('show')

    let confirmBtn = document.querySelector('#confirmModal .confirmBtn').cloneNode(true)
    document.querySelector('#confirmModal .confirmBtn').replaceWith(confirmBtn)

    confirmBtn.addEventListener('click', () => {
        let participant_ids = []
        document.querySelectorAll(`#newList .group[data-id="${group_id}"] .list-group-item`).forEach(item => {
            participant_ids.push(item.dataset.id)
        })

        let data = { 'participants': participant_ids, 'group_id': group_id, 'hash': hash }
        
        if (tournament) {
            data.tournament_id = tournament.id
        }

        $.ajax({
            type: "POST",
            url: apiURL + '/groups/reset',
            data: data,
            success: function (result) {
                if (result.status == 'error') {
                    $('#errorModal .message').html(result.message)
                    $("#errorModal").modal('show');

                    return false
                }

                renderParticipants(result)
            },
            error: function (error) {
                console.log(error);
            }
        }).done(() => {
            $('#confirmModal').modal('hide')

            setTimeout(function () {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    })
}

let deleteGroup = (el) => {
    const group_id = el.parent().data('id')

    $('#confirmModal .message').html(`Are you sure to remove this group "${el.data('name')}"?`)
    $('#confirmModal').modal('show')

    let confirmBtn = document.querySelector('#confirmModal .confirmBtn').cloneNode(true)
    document.querySelector('#confirmModal .confirmBtn').replaceWith(confirmBtn)

    confirmBtn.addEventListener('click', () => {
        let data = {'group_id': group_id, 'hash': hash}
        
        if (tournament) {
            data.tournament_id = tournament.id
        }

        $.ajax({
            type: "POST",
            url: apiURL + '/groups/delete',
            data: data,
            success: function (result) {
                if (result.status == 'error') {
                    $('#errorModal .errorDetails').html(result.message)
                    $("#errorModal").modal('show');

                    return false
                }

                renderParticipants(result)
            },
            error: function (error) {
                console.log(error);
            }
        }).done(() => {
            $('#confirmModal').modal('hide')

            setTimeout(function () {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    })
}

let removeParticipantFromGroup = (el) => {
    const group_id = el.parent().data('group')

    $('#confirmModal .message').html(`Are you sure to remove this participant "${el.data('name')}" from the group "${el.parent().data('name')}"?`)
    $('#confirmModal .text-danger').addClass('d-none')
    $('#confirmModal').modal('show')

    let confirmBtn = document.querySelector('#confirmModal .confirmBtn').cloneNode(true)
    document.querySelector('#confirmModal .confirmBtn').replaceWith(confirmBtn)

    confirmBtn.addEventListener('click', () => {
        let data = {'participant_id': el.data('id'), 'group_id': group_id, 'hash': hash}
        
        if (tournament) {
            data.tournament_id = tournament.id
        }

        $.ajax({
            type: "POST",
            url: apiURL + '/groups/remove-participant',
            data: data,
            success: function (result) {
                if (result.status == 'error') {
                    $('#errorModal .message').html(result.message)
                    $("#errorModal").modal('show');

                    return false
                }

                let group_id = el.parent().data('group')

                renderParticipants(result)

                collapseOutGroup(group_id, 'g')
            },
            error: function (error) {
                console.log(error);
            }
        }).done(() => {
            $('#confirmModal').modal('hide')

            setTimeout(function () {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    })
}

let enableGroupEdit = (el) => {
    var group_id = el.data('id');
    var originalHtml = el.html()

    const nameBox = document.createElement('input');
    const name = el.parent().find('span.name').text();
    nameBox.classList.add('new-group-name', 'form-control');
    nameBox.value = name;

    const buttonWrapper = document.createElement('div');
    const saveBtn = document.createElement('button');
    saveBtn.classList.add('btn', 'btn-primary', 'ms-1');
    saveBtn.textContent = "Update";
    saveBtn.addEventListener('click', event => {
        updateGroup(event)
    });
    buttonWrapper.appendChild(saveBtn);
    buttonWrapper.classList.add('col-auto');

    const cancelBtn = document.createElement('button')
    cancelBtn.classList.add('btn', 'btn-secondary', 'ms-1')
    cancelBtn.textContent = 'Cancel'
    cancelBtn.addEventListener('click', event => {
        event.target.parentElement.parentElement.setAttribute('data-bs-toggle', "collapse")
        event.target.parentElement.parentElement.classList.add('group-name')
        event.target.parentElement.parentElement.innerHTML = originalHtml
    })
    buttonWrapper.appendChild(cancelBtn)

    const imgWrapper = document.createElement('div')
    imgWrapper.setAttribute('class', "group-image-edit")
    const img = el.find('img.group-image')[0]
    const fileInput = document.createElement('input')
    fileInput.type = 'file';
    fileInput.className = 'd-none';
    const deleteImgBtn = document.createElement('button')
    deleteImgBtn.setAttribute('class', "group_image_delete btn")
    deleteImgBtn.innerHTML = `<i class="fa fa-close"></i>`
    
    imgWrapper.appendChild(img)
    imgWrapper.appendChild(fileInput)

    if (img.classList.contains('has-img')) {
        imgWrapper.appendChild(deleteImgBtn)
    }
    
    img.addEventListener('click', event => {
        fileInput.click()
    })
    fileInput.addEventListener('change', event => {
        uploadGroupImage(event.target)
    })
    deleteImgBtn.addEventListener('click', event => {
        removeGroupImage(event)
    })

    el.removeClass('group-name')
    el.html('');
    el.append(imgWrapper)
    el.append(nameBox)
    el.append(buttonWrapper)
    el.removeAttr('data-bs-toggle')
}

let collapseOutGroup = (id, type = null) => {
    let collapseElement
    if (type == 'g') {
        collapseElement = document.querySelector('.list-group[data-group="' + id + '"]')
    } else {
        collapseElement = document.querySelector('.participant[id="' + id + '"]').parentElement
    }

    if (collapseElement)
        collapseElement.classList.add('show')
}