<?php

/**
 * Migration Manager Template
 */

function template_migration_list()
{
    global $context, $txt, $scripturl;

    echo '
    <div id="admin_content">
        <div class="cat_bar">
            <h3 class="catbg">', $txt['mm_migrations_title'], '</h3>
        </div>';

    if (!empty($context['mm_success']))
        echo '
        <div class="infobox">', $context['mm_success'], '</div>';

    if (!empty($context['mm_error']))
        echo '
        <div class="errorbox">', $context['mm_error'], '</div>';

    echo '
        <div class="windowbg">
            <div class="content">
                <table class="table_grid">
                    <thead>
                        <tr class="title_bar">
                            <th scope="col" class="lefttext">', $txt['mm_version'], '</th>
                            <th scope="col" class="lefttext">', $txt['mm_status'], '</th>
                            <th scope="col" class="centertext">', $txt['mm_actions'], '</th>
                        </tr>
                    </thead>
                    <tbody>';

    if (empty($context['migrations']))
    {
        echo '
                        <tr>
                            <td colspan="3" class="centertext">', $txt['mm_no_migrations'], '</td>
                        </tr>';
    }
    else
    {
        foreach ($context['migrations'] as $migration)
        {
            $status_class = '';
            $status_text = '';

            if ($migration['status'] == 'applied') {
                $status_class = 'success';
                $status_text = $txt['mm_status_applied'];
            } elseif ($migration['status'] == 'pending') {
                $status_class = 'warn';
                $status_text = $txt['mm_status_pending'];
            } else {
                $status_class = 'error';
                $status_text = $txt['mm_status_missing'];
            }

            echo '
                        <tr class="windowbg">
                            <td>', $migration['version'], '</td>
                            <td class="', $status_class, '">', $status_text, '</td>
                            <td class="centertext">';

            if ($migration['status'] == 'pending') {
                echo '
                                <a href="', $scripturl, '?action=admin;area=migrations;sa=apply;version=', $migration['version'], ';', $context['session_var'], '=', $context['session_id'], '" class="button">', $txt['mm_apply'], '</a>';
            } elseif ($migration['status'] == 'applied') {
                echo '
                                <a href="', $scripturl, '?action=admin;area=migrations;sa=revert;version=', $migration['version'], ';', $context['session_var'], '=', $context['session_id'], '" class="button">', $txt['mm_revert'], '</a>';
            }

            echo '
                            </td>
                        </tr>';
        }
    }

    echo '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
}
