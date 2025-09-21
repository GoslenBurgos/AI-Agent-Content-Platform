(function( $ ) {
    'use strict';

    // Global variable to store generated ideas
    var generatedIdeas = [];

    $(function() {
        // Load agents on page load
        if ($('#the-list').length) {
            loadAgents();
        }

        // Load content on page load
        if ($('#the-content-list').length) {
            loadContent();
            populateAgentSelector(); // Populate agent dropdown for content generation
        }

        // Load scheduled posts on page load
        if ($('#the-schedule-list').length) {
            loadScheduledPosts();
            populateContentDropdown();
        }

        // Initialize FullCalendar on the Dashboard page
        if ($('#calendar').length && typeof FullCalendar !== 'undefined') {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                editable: true, // Make events draggable
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    var data = { action: 'iacp_get_scheduled_posts', nonce: iacp_ajax.nonce };
                    $.post(iacp_ajax.ajax_url, data, function(response) {
                        if (response.success) {
                            var events = response.data.map(function(post) {
                                return {
                                    title: escapeHtml(post.content_title) + ' - ' + escapeHtml(post.platform),
                                    start: post.publish_date,
                                    extendedProps: { // Store custom data here
                                        postId: post.id
                                    }
                                };
                            });
                            successCallback(events);
                        } else {
                            console.error('Error loading scheduled posts for calendar:', response.data.message);
                            failureCallback();
                        }
                    }).fail(function() {
                        console.error('AJAX error loading scheduled posts for calendar.');
                        failureCallback();
                    });
                },
                eventDrop: function(info) {
                    var postId = info.event.extendedProps.postId;
                    // Format to 'YYYY-MM-DD HH:mm:ss' which is what our DB expects
                    var newDate = info.event.start.toISOString().slice(0, 19).replace('T', ' ');

                    var data = {
                        action: 'iacp_update_scheduled_post_date',
                        nonce: iacp_ajax.nonce,
                        post_id: postId,
                        new_date: newDate
                    };

                    $.post(iacp_ajax.ajax_url, data, function(response) {
                        if (response.success) {
                            showNotice('Post rescheduled successfully!', 'success');
                        } else {
                            showNotice('Error rescheduling post: ' + (response.data.message || 'Unknown error'), 'error');
                            info.revert(); // Revert the event to its original position
                        }
                    }).fail(function() {
                        showNotice('An unexpected server error occurred.', 'error');
                        info.revert();
                    });
                }
            });
            calendar.render();
        }

        // Handle new agent form submission
        $('#add-agent-form').on('submit', function(e) {
            e.preventDefault();

            var formData = {
                action: 'iacp_create_agent',
                nonce: iacp_ajax.nonce,
                name: $('#agent-name').val(),
                role: $('#agent-role').val(),
                experience: $('#agent-experience').val(),
                tasks: $('#agent-tasks').val(),
                prompt: $('#agent-prompt').val()
            };

            $.post(iacp_ajax.ajax_url, formData, function(response) {
                if (response.success) {
                    $('#add-agent-form')[0].reset();
                    loadAgents();
                    showNotice('Agent created successfully.', 'success');
                } else {
                    var errorMessage = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
                    showNotice('Error creating agent: ' + escapeHtml(errorMessage), 'error');
                }
            });
        });

        // Handle agent deletion
        $('#the-list').on('click', '.delete-agent', function(e) {
            e.preventDefault();
            var agentId = $(this).data('agent-id');

            showConfirmationModal('Are you sure you want to delete this agent?', function() {
                var data = {
                    action: 'iacp_delete_agent',
                    nonce: iacp_ajax.nonce,
                    agent_id: agentId
                };

                $.post(iacp_ajax.ajax_url, data, function(response) {
                    if (response.success) {
                        loadAgents();
                        showNotice('Agent deleted successfully.', 'success');
                    } else {
                        var errorMessage = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
                        showNotice('Error deleting agent: ' + escapeHtml(errorMessage), 'error');
                    }
                });
            });
        });

        // Handle agent editing - Show modal and fetch data
        $('#the-list').on('click', '.edit-agent', function(e) {
            e.preventDefault();
            var agentId = $(this).data('agent-id');

            var data = {
                action: 'iacp_get_agent',
                nonce: iacp_ajax.nonce,
                agent_id: agentId
            };

            $.post(iacp_ajax.ajax_url, data, function(response) {
                if (response.success) {
                    var agent = response.data;
                    $('#edit-agent-id').val(agent.id);
                    $('#edit-agent-name').val(agent.name);
                    $('#edit-agent-role').val(agent.role);
                    $('#edit-agent-experience').val(agent.experience);
                    $('#edit-agent-tasks').val(agent.tasks);
                    $('#edit-agent-prompt').val(agent.prompt);
                    $('#edit-agent-modal').show();
                } else {
                    var errorMessage = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
                    showNotice('Error fetching agent data: ' + escapeHtml(errorMessage), 'error');
                }
            });
        });

        // Handle agent editing - Cancel
        $('#cancel-edit-agent').on('click', function() {
            $('#edit-agent-modal').hide();
        });

        // Handle agent editing - Form submission
        $('#edit-agent-form').on('submit', function(e) {
            e.preventDefault();

            var formData = {
                action: 'iacp_update_agent',
                nonce: iacp_ajax.nonce,
                agent_id: $('#edit-agent-id').val(),
                name: $('#edit-agent-name').val(),
                role: $('#edit-agent-role').val(),
                experience: $('#edit-agent-experience').val(),
                tasks: $('#edit-agent-tasks').val(),
                prompt: $('#edit-agent-prompt').val()
            };

            $.post(iacp_ajax.ajax_url, formData, function(response) {
                if (response.success) {
                    $('#edit-agent-modal').hide();
                    loadAgents();
                    showNotice('Agent updated successfully.', 'success');
                } else {
                    var errorMessage = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
                    showNotice('Error updating agent: ' + escapeHtml(errorMessage), 'error');
                }
            });
        });

        // Handle editorial profile form submission
        $(document).on('click', '#submit-editorial-profile', function(e) {
            e.preventDefault();

            var formData = {
                action: 'iacp_save_editorial_profile',
                nonce: iacp_ajax.nonce,
                target_audience: $('#target-audience').val(),
                voice_tone: $('#voice-tone').val(),
                style_guide: $('#style-guide').val(),
                banned_words: $('#banned-words').val()
            };

            $.post(iacp_ajax.ajax_url, formData, function(response) {
                if (response.success) {
                    showNotice('Editorial profile saved successfully.', 'success');
                } else {
                    var errorMessage = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
                    showNotice('Error saving editorial profile: ' + escapeHtml(errorMessage), 'error');
                }
            });
        });

        // Handle idea generator form submission
        $('#idea-generator-form').on('submit', function(e) {
            e.preventDefault();

            var formData = {
                action: 'iacp_generate_ideas',
                nonce: iacp_ajax.nonce,
                keywords: $('#idea-keywords').val()
            };

            var container = $('#generated-ideas-container');
            container.html('<div class="iacp-spinner-container"><div class="iacp-spinner"></div><p>Generating and evaluating ideas... (This may take a moment)</p></div>');

            $.post(iacp_ajax.ajax_url, formData, function(response) {
                if (response.success) {
                    generatedIdeas = response.data; // Store ideas globally
                    container.empty(); // Clear the loading message

                    if (generatedIdeas && generatedIdeas.length > 0) {
                        var table = '<table class="wp-list-table widefat fixed striped">' +
                            '<thead><tr><th>Title</th><th>Score</th><th>Details</th><th>Actions</th></tr></thead>' +
                            '<tbody>';

                        $.each(generatedIdeas, function(index, idea) {
                            var details = '<ul>' +
                                '<li><strong>Simple (5yo):</strong> ' + escapeHtml(idea.is_simple || 'N/A') + '</li>' +
                                '<li><strong>Audience (50-100):</strong> ' + escapeHtml(idea.audience_interest || 'N/A') + '</li>' +
                                '<li><strong>Viral Ref:</strong> ' + escapeHtml(idea.is_viral_reference || 'N/A') + '</li>' +
                                '<li><strong>Trending:</strong> ' + escapeHtml(idea.is_trending || 'N/A') + '</li>' +
                                '<li><strong>Controversial:</strong> ' + escapeHtml(idea.is_controversial || 'N/A') + '</li>' +
                                '<hr><li><strong>Hook:</strong> ' + escapeHtml(idea.hook || 'N/A') + '</li>' +
                                '<li><strong>Story:</strong> ' + escapeHtml(idea.story || 'N/A') + '</li>' +
                                '<li><strong>Moral:</strong> ' + escapeHtml(idea.moral || 'N/A') + '</li>' +
                                '<li><strong>CTA:</strong> ' + escapeHtml(idea.cta || 'N/A') + '</li>' +
                            '</ul>';

                            table += '<tr>' +
                                '<td>' + escapeHtml(idea.title || 'No title generated') + '</td>' +
                                '<td>' + escapeHtml(idea.score || 'N/A') + '/10</td>' +
                                '<td>' + details + '</td>' +
                                '<td><button class="button button-primary select-idea" data-index="' + index + '">Select</button></td>' +
                            '</tr>';
                        });

                        table += '</tbody></table>';
                        container.html(table);
                    } else {
                        container.html('<p>No ideas could be generated for these keywords.</p>');
                    }
                } else {
                    var errorMessage = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
                    container.html('<div class="notice notice-error"><p><strong>Error:</strong> ' + escapeHtml(errorMessage) + '</p></div>');
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                container.html('<div class="notice notice-error"><p><strong>Error de AJAX:</strong> ' + escapeHtml(textStatus + ' - ' + errorThrown) + '</p></div>');
            });
        });

        // Handle click on "Select" button for an idea
        $('#generated-ideas-container').on('click', '.select-idea', function() {
            var index = $(this).data('index');
            var idea = generatedIdeas[index]; // Retrieve the full idea object from the global array
            $('#content-title').val(idea.title);
            $('#content-theme').val(idea.story || ''); // Populate theme with story

            // Populate hidden fields for virality score and status
            $('#iacp_virality_score').val(idea.score || 0);
            $('#iacp_content_status').val(idea.score >= 7 ? 'approved' : 'rejected');
        });

        // Handle content generator form submission
        $('#content-generator-form').on('submit', function(e) {
            e.preventDefault();

            var submitButton = $(this).find('input[type=submit]');
            submitButton.val('Generating Content...').prop('disabled', true);

            var formData = {
                action: 'iacp_generate_content',
                nonce: iacp_ajax.nonce,
                draft_agent_id: $('#draft-agent-selector').val(),
                seo_agent_id: $('#seo-agent-selector').val(),
                copy_agent_id: $('#copy-agent-selector').val(),
                image_agent_id: $('#image-agent-selector').val(),
                title_agent_id: $('#title-agent-selector').val(),
                title: $('#content-title').val(),
                theme: $('#content-theme').val(),
                virality_score: $('#iacp_virality_score').val(), // Include virality score
                content_status: $('#iacp_content_status').val() // Include content status
            };

            $.post(iacp_ajax.ajax_url, formData, function(response) {
                if (response.success) {
                    showNotice('Content generated and saved as a draft!', 'success');
                    $('#content-generator-form')[0].reset();
                    loadContent();
                } else {
                    var errorMessage = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
                    showNotice('Error generating content: ' + errorMessage, 'error');
                }
            }).fail(function() {
                showNotice('An unexpected server error occurred while generating content.', 'error');
            }).always(function() {
                submitButton.val('Generate Content').prop('disabled', false);
            });
        });

        // Handle delete content
        $('#the-content-list').on('click', '.delete-content', function(e) {
            e.preventDefault();
            var contentId = $(this).data('content-id');

            showConfirmationModal('Are you sure you want to delete this content?', function() {
                var data = {
                    action: 'iacp_delete_content',
                    nonce: iacp_ajax.nonce,
                    content_id: contentId
                };

                $.post(iacp_ajax.ajax_url, data, function(response) {
                    if (response.success) {
                        loadContent();
                        showNotice('Content deleted successfully.', 'success');
                    } else {
                        var errorMessage = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
                        showNotice('Error deleting content: ' + escapeHtml(errorMessage), 'error');
                    }
                });
            });
        });

        // Handle View content
        $('#the-content-list').on('click', '.view-content', function(e) {
            e.preventDefault();
            var contentId = $(this).data('content-id');

            var data = {
                action: 'iacp_get_single_content',
                nonce: iacp_ajax.nonce,
                content_id: contentId
            };

            $.post(iacp_ajax.ajax_url, data, function(response) {
                if (response.success) {
                    // Populate and show the modal
                    $('#view-content-modal-title').text(response.data.title);
                    // Escape content first, then replace newlines with <br> for proper display
                    $('#view-content-modal-body').html(escapeHtml(response.data.content).replace(/\n/g, '<br>'));
                    $('#view-content-modal').show();
                } else {
                    showNotice('Error viewing content: ' + (response.data && response.data.message ? response.data.message : 'Unknown error'), 'error');
                }
            });
        });

        // Handle Schedule content
        $('#the-content-list').on('click', '.schedule-content', function(e) {
            e.preventDefault();
            var contentId = $(this).data('content-id');

            // Get the title from the same row for convenience
            var contentTitle = $(this).closest('tr').find('td:first').text();

            // Navigate to Social Media Planner tab
            window.location.href = 'admin.php?page=ia-agent-content-platform-social-media-planner';

            // Store data in session storage to populate form on the other page
            sessionStorage.setItem('iacp_schedule_content_id', contentId);
            sessionStorage.setItem('iacp_schedule_content_title', contentTitle);
        });

        // Check for scheduled content data on Social Media Planner page load
        if ($('#social-media-planner-wrapper').length) {
            var scheduledContentId = sessionStorage.getItem('iacp_schedule_content_id');
            var scheduledContentTitle = sessionStorage.getItem('iacp_schedule_content_title');

            if (scheduledContentId && scheduledContentTitle) {
                // Populate the dropdown. First, ensure the option exists.
                var select = $('#post-content-id');
                if (select.find('option[value="' + scheduledContentId + '"]').length === 0) {
                    select.append('<option value="' + scheduledContentId + '">' + scheduledContentTitle + '</option>');
                }
                select.val(scheduledContentId);

                // Clear session storage
                sessionStorage.removeItem('iacp_schedule_content_id');
                sessionStorage.removeItem('iacp_schedule_content_title');
            }
        }

        // Handle schedule post form submission
        $('#schedule-post-form').on('submit', function(e) {
            e.preventDefault();
 
            var submitButton = $(this).find('#submit-schedule');
            submitButton.val('Scheduling...').prop('disabled', true);
 
            // Correctly get all checked platforms as an array
            var selectedPlatforms = $('input[name="platforms[]"]:checked').map(function() {
                return this.value;
            }).get();
 
            if (selectedPlatforms.length === 0) {
                showNotice('Please select at least one platform to schedule.', 'error');
                submitButton.val('Schedule Post').prop('disabled', false);
                return;
            }
 
            var formData = {
                action: 'iacp_schedule_post',
                nonce: iacp_ajax.nonce,
                content_id: $('#post-content-id').val(),
                platforms: selectedPlatforms, // Use 'platforms' key with the array
                message: $('#post-message').val(),
                publish_date: $('#post-publish-date').val()
            };
 
            $.post(iacp_ajax.ajax_url, formData, function(response) {
                if (response.success) {
                    var successMessages = [];
                    var errorMessages = [];
 
                    // The response.data contains the results for each platform
                    $.each(response.data, function(platform, result) {
                        if (result.success) {
                            successMessages.push('Successfully scheduled for ' + platform + '.');
                        } else {
                            errorMessages.push('Failed to schedule for ' + platform + ': ' + (result.message || 'Unknown error'));
                        }
                    });
 
                    if (errorMessages.length > 0) {
                        showNotice("There were some issues:\n" + errorMessages.join('\n'), 'error');
                    } else {
                        showNotice('All posts scheduled successfully!', 'success');
                    }
 
                    $('#schedule-post-form')[0].reset();
                    // Re-check the blog platform by default after reset
                    $('input[name="platforms[]"][value="blog"]').prop('checked', true);
                    loadScheduledPosts();
                } else {
                    var errorMessage = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
                    showNotice('Error: ' + errorMessage, 'error');
                }
            }).fail(function() {
                showNotice('An unexpected server error occurred.', 'error');
            }).always(function() {
                submitButton.val('Schedule Post').prop('disabled', false);
            });
        });

        // Handle scheduled post deletion
        $('#the-schedule-list').on('click', '.delete-scheduled-post', function(e) {
            e.preventDefault();
            var postId = $(this).data('post-id');

            showConfirmationModal('Are you sure you want to delete this scheduled post?', function() {
                var data = {
                    action: 'iacp_delete_scheduled_post',
                    nonce: iacp_ajax.nonce,
                    post_id: postId
                };

                $.post(iacp_ajax.ajax_url, data, function(response) {
                    if (response.success) {
                        loadScheduledPosts();
                        showNotice('Scheduled post deleted successfully.', 'success');
                    } else {
                        var errorMessage = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
                        showNotice('Error deleting scheduled post: ' + escapeHtml(errorMessage), 'error');
                    }
                });
            });
        });

        // Handle AI social message generation
        $('#generate-social-message').on('click', function(e) {
            e.preventDefault();
            var contentId = $('#post-content-id').val();
            var button = $(this);

            if (!contentId) {
                showNotice('Please select an article first.', 'error');
                return;
            }

            button.text('Generating...').prop('disabled', true);

            var data = {
                action: 'iacp_generate_social_post_suggestion',
                nonce: iacp_ajax.nonce,
                content_id: contentId
            };

            $.post(iacp_ajax.ajax_url, data, function(response) {
                if (response.success) {
                    $('#post-message').val(response.data.suggestion).focus(); // Set value and focus
                    showNotice('Social media post suggestion generated!', 'success');
                } else {
                    var errorMessage = response.data && response.data.message ? response.data.message : 'An unknown error occurred.';
                    showNotice('Error: ' + escapeHtml(errorMessage), 'error');
                }
            }).fail(function() {
                showNotice('An unexpected server error occurred.', 'error');
            }).always(function() {
                button.html('✨ Generate with AI').prop('disabled', false);
            });
        });

        // Handle closing the view content modal
        $(document).on('click', '#view-content-modal-close', function() {
            $('#view-content-modal').hide();
        });

        // Handle View content history - More robust event delegation
        $(document).on('click', '.history-content', function(e) {
            e.preventDefault();
            var contentId = $(this).data('content-id');
            var modalBody = $('#content-history-modal-body');
            modalBody.html('<div class="iacp-spinner-container"><div class="iacp-spinner"></div></div>');
            $('#content-history-modal').show();

            var data = {
                action: 'iacp_get_content_versions',
                nonce: iacp_ajax.nonce,
                content_id: contentId
            };

            $.post(iacp_ajax.ajax_url, data, function(response) {
                modalBody.empty();
                if (response.success && response.data.length > 0) {
                    var historyHtml = '<ul class="iacp-version-list">';
                    $.each(response.data, function(index, version) {
                        historyHtml += '<li>' +
                            '<div><strong>' + escapeHtml(version.created_at) + '</strong><br>' +
                            '<span>' + escapeHtml(version.version_note) + '</span></div>' +
                            '<div class="version-actions">' +
                                '<button class="button restore-version" data-version-id="' + version.id + '">Restore</button>' +
                            '</div>' +
                        '</li>';
                    });
                    historyHtml += '</ul>';
                    modalBody.html(historyHtml);
                } else {
                    modalBody.html('<p>No version history found for this content.</p>');
                }
            });
        });

        // Handle Restore version - More robust event delegation
        $(document).on('click', '.restore-version', function() {
            var versionId = $(this).data('version-id');
            var button = $(this);

            showConfirmationModal('Are you sure you want to restore this version? The current content will be overwritten.', function() {
                button.text('Restoring...').prop('disabled', true);
                var data = { action: 'iacp_restore_content_version', nonce: iacp_ajax.nonce, version_id: versionId };
                $.post(iacp_ajax.ajax_url, data, function(response) {
                    if (response.success) {
                        showNotice('Content restored successfully!', 'success');
                        $('#content-history-modal').hide();
                    } else {
                        showNotice('Error restoring version: ' + (response.data.message || 'Unknown error'), 'error');
                    }
                }).always(function() { button.text('Restore').prop('disabled', false); });
            });
        });

        // Handle closing the history modal - More robust event delegation
        $(document).on('click', '#content-history-modal-close', function() {
            $('#content-history-modal').hide();
        });
    });

    function loadAgents() {
        loadTableData({
            tableBodySelector: '#the-list',
            ajaxAction: 'iacp_get_agents',
            columns: 3,
            noItemsMessage: 'No agents found.',
            rowBuilderCallback: function(agent) {
                return '<tr>' +
                    '<td>' + escapeHtml(agent.name) + '</td>' +
                    '<td>' + escapeHtml(agent.role) + '</td>' +
                    '<td>' +
                        '<a href="#" class="edit-agent" data-agent-id="' + agent.id + '">Edit</a> | ' +
                        '<a href="#" class="delete-agent" data-agent-id="' + agent.id + '" style="color:red;">Delete</a>' +
                    '</td>' +
                '</tr>';
            }
        });
    }

    function loadContent() {
        loadTableData({
            tableBodySelector: '#the-content-list',
            ajaxAction: 'iacp_get_content',
            columns: 6, // Corrected from 5 to 6 to match the table structure
            noItemsMessage: 'No content generated yet.',
            rowBuilderCallback: function(item) {
                return '<tr>' +
                    '<td>' + escapeHtml(item.title) + '</td>' +
                    '<td>' + escapeHtml(item.views) + '</td>' + // Added missing views column
                    '<td>' + escapeHtml(item.virality_score) + '</td>' +
                    '<td>' + escapeHtml(item.status) + '</td>' +
                    '<td>' +
                        '<a href="#" class="view-content" data-content-id="' + item.id + '">View</a> | ' +
                        '<a href="#" class="history-content" data-content-id="' + item.id + '">History</a> | ' +
                        '<a href="#" class="schedule-content" data-content-id="' + item.id + '">Schedule</a>' +
                    '</td>' +
                    '<td><button class="button delete-content" data-content-id="' + item.id + '">Delete</button></td>' +
                '</tr>';
            }
        });
    }

    function loadScheduledPosts() {
        loadTableData({
            tableBodySelector: '#the-schedule-list',
            ajaxAction: 'iacp_get_scheduled_posts',
            columns: 5,
            noItemsMessage: 'No posts scheduled yet.',
            debug: true, // Enable extra logging for this table
            rowBuilderCallback: function(post) {
                return '<tr>' +
                    '<td>' + escapeHtml(post.content_title || 'N/A') + '</td>' +
                    '<td>' + escapeHtml(post.platform) + '</td>' +
                    '<td>' + escapeHtml(post.message) + '</td>' +
                    '<td>' + escapeHtml(post.publish_date) + '</td>' +
                    '<td>' +
                        '<a href="#" class="delete-scheduled-post" data-post-id="' + post.id + '" style="color:red;">Delete</a>' +
                    '</td>' +
                '</tr>';
            }
        });
    }

    function populateAgentSelector() {
        var data = { action: 'iacp_get_agents', nonce: iacp_ajax.nonce };
        var selectors = $('.agent-selector'); // Target all agent selectors

        selectors.each(function() {
            var select = $(this);
            $.post(iacp_ajax.ajax_url, data, function(response) {
                if (response.success) {
                    var agents = response.data;
                    var firstOption = select.find('option:first'); // Keep the first option (e.g., "Skip this step...")
                    select.empty().append(firstOption); // Restore the first option
                    if (agents.length > 0) {
                        $.each(agents, function(index, agent) {
                            select.append($('<option>').val(agent.id).text(agent.name + ' - ' + agent.role));
                        });
                    }
                } // No else, to avoid overwriting "Skip" with "Could not load"
            });
        });
    }

    function populateContentDropdown() {
        var data = { action: 'iacp_get_content', nonce: iacp_ajax.nonce };
        $.post(iacp_ajax.ajax_url, data, function(response) {
            if (response.success) {
                var content = response.data;
                var select = $('#post-content-id');
                select.empty();
                select.append('<option value="">Select an article</option>');
                if (content.length > 0) {
                    $.each(content, function(index, item) {
                        if (item.status === 'approved') {
                            // Use jQuery object creation to prevent XSS in titles
                            select.append($('<option>').val(item.id).text(item.title));
                        }
                    });
                }
            }
        });
    }

    function showNotice(message, type) {
        var notice = $("<div class=\"iacp-notice\"></div>");
        notice.addClass(type);
        notice.text(message);
        $('body').append(notice);
        notice.fadeIn();
        setTimeout(function() {
            notice.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    function showConfirmationModal(message, confirmCallback) {
        // Remove any existing modal
        $('.iacp-modal-overlay').remove();

        // Create modal HTML
        var modalHtml = `
            <div class="iacp-modal-overlay">
                <div class="iacp-modal">
                    <h3>Confirmation</h3>
                    <p>${message}</p>
                    <div class="iacp-modal-buttons">
                        <button class="button button-primary" id="iacp-modal-confirm">Confirm</button>
                        <button class="button" id="iacp-modal-cancel">Cancel</button>
                    </div>
                </div>
            </div>
        `;

        // Append to body and show
        $('body').append(modalHtml);
        $('.iacp-modal-overlay').fadeIn(200);

        // Handle button clicks
        $('#iacp-modal-confirm').on('click', function() {
            if (typeof confirmCallback === 'function') {
                confirmCallback();
            }
            $('.iacp-modal-overlay').fadeOut(200, function() {
                $(this).remove();
            });
        });

        $('#iacp-modal-cancel').on('click', function() {
            $('.iacp-modal-overlay').fadeOut(200, function() {
                $(this).remove();
            });
        });
    }

    /**
     * Generic function to load data into a table via AJAX.
     * @param {object} options - Configuration object.
     * @param {string} options.tableBodySelector - jQuery selector for the table body (tbody).
     * @param {string} options.ajaxAction - The WordPress AJAX action to call.
     * @param {number} options.columns - The number of columns for the colspan attribute.
     * @param {string} options.noItemsMessage - Message to display when no data is returned.
     * @param {function} options.rowBuilderCallback - A function that receives a data item and returns a <tr> HTML string.
     */
    function loadTableData(options) {
        var tableBody = $(options.tableBodySelector);
        if (!tableBody.length) {
            if (options.debug) console.log('Table body not found for selector:', options.tableBodySelector);
            return; // Don't run if the table isn't on the page
        }

        // 1. Show spinner
        var spinnerRow = '<tr><td colspan="' + options.columns + '" class="iacp-spinner-container"><div class="iacp-spinner"></div></td></tr>';
        tableBody.html(spinnerRow);

        // 2. Prepare AJAX call
        var data = {
            action: options.ajaxAction,
            nonce: iacp_ajax.nonce
        };

        // 3. Make AJAX call
        $.post(iacp_ajax.ajax_url, data, function(response) {
            if (options.debug) console.log('AJAX response for ' + options.ajaxAction, response);
            tableBody.empty(); // Clear spinner

            if (response.success && response.data && response.data.length > 0) {
                // 4. Build and append rows
                $.each(response.data, function(index, item) {
                    var rowHtml = options.rowBuilderCallback(item);
                    tableBody.append(rowHtml);
                });
            } else {
                // 5. Show no items message
                var noItemsRow = '<tr><td colspan="' + options.columns + '">' + escapeHtml(options.noItemsMessage) + '</td></tr>';
                tableBody.append(noItemsRow);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            if (options.debug) console.error('AJAX call failed for ' + options.ajaxAction, textStatus, errorThrown);
            tableBody.empty();
            var errorRow = '<tr><td colspan="' + options.columns + '">Failed to load data. Please try again.</td></tr>';
            tableBody.append(errorRow);
        });
    }

    /**
     * Escapes HTML special characters in a string to prevent XSS.
     * @param {string | number | null | undefined} unsafe The string to escape.
     * @returns {string} The escaped string.
     */
    function escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') {
            // If it's not a string (e.g., a number like virality_score), convert it to one.
            // This also handles null/undefined gracefully by turning them into empty strings.
            unsafe = String(unsafe || '');
        }
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
     }
})( jQuery );
