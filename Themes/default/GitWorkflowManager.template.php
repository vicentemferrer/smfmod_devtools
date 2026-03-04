<?php

/**
 * Git Workflow Manager Template
 */

function template_gwm_list()
{
    global $context, $txt, $scripturl;

    echo '
    <div id="admin_content">
        <div class="cat_bar">
            <h3 class="catbg">', $txt['gwm_title'], '</h3>
        </div>';

    if (!empty($context['gwm_success']))
        echo '
        <div class="infobox">', $context['gwm_success'], '</div>';

    if (!empty($context['gwm_error']))
        echo '
        <div class="errorbox">', $context['gwm_error'], '</div>';

    echo '
        <div class="windowbg">
            <div class="content">
                <table class="table_grid">
                    <thead>
                        <tr class="title_bar">
                            <th scope="col" class="lefttext">', $txt['gwm_version'], '</th>
                            <th scope="col" class="lefttext">', $txt['gwm_status'], '</th>
                            <th scope="col" class="centertext">', $txt['gwm_actions'], '</th>
                        </tr>
                    </thead>
                    <tbody>';

    if (empty($context['migrations']))
    {
        echo '
                        <tr>
                            <td colspan="3" class="centertext">', $txt['gwm_no_migrations'], '</td>
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
                $status_text = $txt['gwm_status_applied'];
            } elseif ($migration['status'] == 'pending') {
                $status_class = 'warn';
                $status_text = $txt['gwm_status_pending'];
            } else {
                $status_class = 'error';
                $status_text = $txt['gwm_status_missing'];
            }

            echo '
                        <tr class="windowbg">
                            <td>', $migration['version'], '</td>
                            <td class="', $status_class, '">', $status_text, '</td>
                            <td class="centertext">';

            if ($migration['status'] == 'pending') {
                echo '
                                <a href="', $scripturl, '?action=admin;area=gwm;sa=apply;version=', $migration['version'], ';', $context['session_var'], '=', $context['session_id'], '" class="button">', $txt['gwm_apply'], '</a>';
            } elseif ($migration['status'] == 'applied') {
                echo '
                                <a href="', $scripturl, '?action=admin;area=gwm;sa=revert;version=', $migration['version'], ';', $context['session_var'], '=', $context['session_id'], '" class="button">', $txt['gwm_revert'], '</a>';
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
