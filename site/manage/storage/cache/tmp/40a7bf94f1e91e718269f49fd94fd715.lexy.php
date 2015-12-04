<?php $app->start('header'); ?>

    <?php echo  $app->assets(['assets:vendor/uikit/js/components/sortable.min.js'], $app['cockpit/version']) ; ?>
    <?php echo  $app->assets(['galleries:assets/galleries.js','galleries:assets/js/index.js'], $app['cockpit/version']) ; ?>

    <style>

        #groups-list li {
            position: relative;
            overflow: hidden;
        }
        .group-actions {
            position: absolute;
            display:none;
            min-width: 60px;
            text-align: right;
            top: 5px;
            right: 10px;
        }

        .group-actions a { font-size: 11px; }

        #groups-list li.uk-active .group-actions,
        #groups-list li:hover .group-actions { display:block; }
        #groups-list li:hover .group-actions a { color: #666; }
        #groups-list li.uk-active a,
        #groups-list li.uk-active .group-actions a { color: #fff; }


    </style>

<?php $app->end('header'); ?>

<div data-ng-controller="galleries" ng-cloak>

    <nav class="uk-navbar uk-margin-large-bottom">
        <span class="uk-hidden-small uk-navbar-brand"><?php echo $app("i18n")->get('Galleries'); ?></span>
        <div class="uk-hidden-small uk-navbar-content" data-ng-show="galleries && galleries.length">
            <form class="uk-form uk-margin-remove uk-display-inline-block">
                <div class="uk-form-icon">
                    <i class="uk-icon-filter"></i>
                    <input type="text" placeholder="<?php echo $app("i18n")->get('Filter by name...'); ?>" data-ng-model="filter">
                </div>
            </form>
        </div>
        <?php if ($app->module("auth")->hasaccess("Galleries", 'create.gallery')) { ?>
        <ul class="uk-navbar-nav">
            <li><a href="<?php $app->route('/galleries/gallery'); ?>" title="<?php echo $app("i18n")->get('Add gallery'); ?>" data-uk-tooltip="{pos:'right'}"><i class="uk-icon-plus-circle"></i></a></li>
        </ul>
        <?php } ?>
        <div class="uk-navbar-flip" data-ng-if="galleries && galleries.length">
            <div class="uk-navbar-content">
                <div class="uk-button-group">
                    <button class="uk-button" data-ng-class="mode=='list' ? 'uk-button-primary':''" data-ng-click="setListMode('list')" title="<?php echo $app("i18n")->get('List mode'); ?>" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-th"></i></button>
                    <button class="uk-button" data-ng-class="mode=='table' ? 'uk-button-primary':''" data-ng-click="setListMode('table')" title="<?php echo $app("i18n")->get('Table mode'); ?>" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-th-list"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <div class="uk-grid uk-grid-divider" data-uk-grid-margin data-ng-show="galleries && galleries.length">

        <div class="uk-width-medium-1-4">
            <div class="uk-panel">
                <ul class="uk-nav uk-nav-side uk-nav-plain" ng-show="groups.length">
                    <li class="uk-nav-header"><?php echo $app("i18n")->get("Groups"); ?></li>
                    <li ng-class="activegroup=='-all' ? 'uk-active':''" ng-click="(activegroup='-all')"><a><?php echo $app("i18n")->get("All galleries"); ?></a></li>
                </ul>

                <ul id="groups-list" class="uk-nav uk-nav-side uk-animation-fade uk-sortable" ng-show="groups.length" data-uk-sortable>
                    <li ng-repeat="group in groups" ng-class="$parent.activegroup==group ? 'uk-active':''" ng-click="($parent.activegroup=group)" draggable="true">
                        <a><i class="uk-icon-bars" style="cursor:move;"></i> {{ group }}</a>
                        <?php if ($app->module("auth")->hasaccess("Galleries", 'create.gallery')) { ?>
                        <ul class="uk-subnav group-actions uk-animation-slide-right">
                            <li><a href="#" ng-click="editGroup(group, $index)"><i class="uk-icon-pencil"></i></a></li>
                            <li><a href="#" ng-click="removeGroup($index)"><i class="uk-icon-trash-o"></i></a></li>
                        </ul>
                        <?php } ?>
                    </li>
                </ul>

                <div class="uk-text-muted" ng-show="!groups.length">
                    <?php echo $app("i18n")->get('Create groups to organize your galleries.'); ?>
                </div>

                <?php if ($app->module("auth")->hasaccess("Galleries", 'create.gallery')) { ?>
                <hr>
                <div class="uk-margin-top">
                    <button class="uk-button uk-button-success" title="<?php echo $app("i18n")->get('Create new group'); ?>" data-uk-tooltip="{pos:'right'}" ng-click="addGroup()"><i class="uk-icon-plus-circle"></i></button>
                </div>
                <?php } ?>
            </div>
        </div>
        <div class="uk-width-medium-3-4">

            <div class="uk-margin-bottom">
                <span class="uk-badge app-badge">{{ (activegroup=='-all' ? '<?php echo $app("i18n")->get("All galleries"); ?>' : activegroup) }}</span>
            </div>

            <div class="uk-grid uk-grid-small" data-uk-grid-match data-ng-if="galleries && galleries.length && mode=='list'">
                <div class="uk-width-1-1 uk-width-medium-1-3 uk-grid-margin" data-ng-repeat="gallery in galleries track by gallery._id" data-ng-show="matchName(gallery.name) && inGroup(gallery.group)">

                    <div class="app-panel">

                        <a class="uk-link-muted" href="<?php $app->route('/galleries/gallery'); ?>/{{ gallery._id }}"><strong>{{ gallery.name }}</strong></a>

                        <div class="uk-margin">
                            <span class="uk-badge app-badge" title="Last update">{{ gallery.modified |fmtdate:'d M, Y H:i' }}</span>
                        </div>

                        <div style="min-height:30px;">
                            <div class="uk-thumbnail uk-rounded uk-thumb-small uk-margin-small-right" data-ng-repeat="image in gallery.images" ng-if="$index < 6">
                                <img ng-src="<?php $app->route('/mediamanager/thumbnail'); ?>/{{ image.path|base64 }}/20/20" width="20" height="20" title="{{ image.path }}">
                            </div>
                        </div>

                        <div class="app-panel-box docked-bottom">

                            <div class="uk-link" data-uk-dropdown="{mode:'click'}">
                                <i class="uk-icon-bars"></i>
                                <div class="uk-dropdown">
                                    <ul class="uk-nav uk-nav-dropdown uk-nav-parent-icon">
                                        <li><a href="<?php $app->route('/galleries/gallery'); ?>/{{ gallery._id }}"><i class="uk-icon-pencil"></i> <?php echo $app("i18n")->get('Edit gallery'); ?></a></li>
                                        <?php if ($app->module("auth")->hasaccess("Galleries", 'create.gallery')) { ?>
                                        <li class="uk-nav-divider"></li>
                                        <li class="uk-danger"><a data-ng-click="remove($index, gallery)" href="#"><i class="uk-icon-minus-circle"></i> <?php echo $app("i18n")->get('Delete gallery'); ?></a></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-panel" data-ng-if="galleries && galleries.length && mode=='table'">
                <table class="uk-table uk-table-striped" multiple-select="{model:galleries}">
                    <thead>
                        <tr>
                            <th width="10"><input class="js-select-all" type="checkbox"></th>
                            <th><?php echo $app("i18n")->get('Gallery'); ?></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="js-multiple-select" data-ng-repeat="gallery in galleries track by gallery._id" data-ng-show="matchName(gallery.name) && inGroup(gallery.group)">
                            <td><input class="js-select" type="checkbox"></td>
                            <td>
                                <a href="<?php $app->route('/galleries/gallery'); ?>/{{ gallery._id }}">{{ gallery.name }}</a>
                            </td>
                            <td>
                                <div class="uk-thumbnail uk-rounded uk-thumb-small uk-margin-small-right" data-ng-repeat="image in gallery.images" ng-if="$index < 6">
                                    <img ng-src="<?php $app->route('/mediamanager/thumbnail'); ?>/{{ image.path|base64 }}/20/20" width="20" height="20" title="{{ image.path }}">
                                </div>
                            </td>
                            <td align="right">
                                <?php if ($app->module("auth")->hasaccess("Galleries", 'create.gallery')) { ?>
                                <a class="uk-text-danger" data-ng-click="remove($index, gallery)" href="#" title="<?php echo $app("i18n")->get('Delete gallery'); ?>" data-uk-tooltip="{pos:'bottom'}"><i class="uk-icon-minus-circle js-ignore-select"></i></a>
                                <?php } ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="uk-margin-top">
                    <button class="uk-button uk-button-danger" data-ng-click="removeSelected()" data-ng-show="selected"><i class="uk-icon-trash-o"></i> <?php echo $app("i18n")->get('Delete'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="uk-text-center app-panel" data-ng-show="galleries && !galleries.length">
        <h2><i class="uk-icon-picture-o"></i></h2>
        <p class="uk-text-large">
            <?php echo $app("i18n")->get('You don\'t have any galleries created.'); ?>
        </p>
        <?php if ($app->module("auth")->hasaccess("Galleries", 'create.gallery')) { ?>
        <a href="<?php $app->route('/galleries/gallery'); ?>" class="uk-button uk-button-success uk-button-large"><?php echo $app("i18n")->get('Create a gallery'); ?></a>
        <?php } ?>
    </div>

</div>