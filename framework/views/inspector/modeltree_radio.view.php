<?php
/**
 * NOVIUS OS - Web OS for digital communication
 *
 * @copyright  2011 Novius
 * @license    GNU Affero General Public License v3 or (at your option) any later version
 *             http://www.gnu.org/licenses/agpl-3.0.html
 * @link http://www.novius-os.org
 */
    empty($attributes) and $attributes = array();
    empty($attributes['id']) and $attributes['id'] = uniqid('temp_');
?>
<div <?= array_to_attr($attributes); ?>><table class="nos-treegrid"></table></div>
<script type="text/javascript">
    require(
        ['jquery', 'jquery-nos-treegrid'],
        function( $ ) {
            $(function() {
                var params = <?= \Format::forge()->to_json($params) ?>,
                    id = <?= \Format::forge($attributes['id'])->to_json() ?>,
                    container = $('#' + id)
                        .css({
                            height: params.height || '150px',
                            width: params.width || ''
                        }),
                    table = container.find('table'),
                    connector = container.closest('.nos-dispatcher, body')
                        .on('contextChange', function() {
                            listenReloadEvent();
                            table.nostreegrid('option', 'treeOptions', {
                                context : connector.data('nosContext') || ''
                            });
                        }),
                    rendered = false,
                    listenReloadEvent = function() {
                        if (params.reloadEvent) {
                            container.nosUnlistenEvent('inspector' + id);
                            var match = {
                                name : params.reloadEvent
                            };
                            if (connector.data('nosContext')) {
                                match['context'] = connector.data('nosContext');
                            }
                            container.nosListenEvent(match, function() {
                                table.nostreegrid('reload');
                            }, 'inspector' + id);
                        }
                    },
                    init = function() {
                        listenReloadEvent();

                        table.nostreegrid({
                                sortable : false,
                                movable : false,
                                urlJson : params.urlJson,
                                treeColumnIndex : 1,
                                treeOptions : $.extend(true, {
                                    context : connector.data('nosContext') || ''
                                }, params.treeOptions || {}),
                                preOpen : params.selected || {},
                                columnsAutogenerationMode : 'none',
                                scrollMode : 'auto',
                                cellStyleFormatter: function(args) {
                                    if (args.$cell.is('td')) {
                                        args.$cell.removeClass("ui-state-highlight");
                                    }
                                },
                                rowStyleFormatter : function(args) {
                                    if (args.type == $.wijmo.wijgrid.rowType.header) {
                                        args.$rows.hide();
                                    }
                                },
                                currentCellChanged : function(e) {
                                    var row = $(e.target).nostreegrid("currentCell").row(),
                                        data = row ? row.data : false;

                                    if (data && rendered) {
                                        params.selected.id = data._id;
                                        row.$rows.find(':radio[value=' + params.selected.id + ']').prop('checked', true).triggerHandler('click');
                                    }
                                },
                                rendering : function() {
                                    rendered = false;
                                },
                                rendered : function() {
                                    rendered = true;
                                    table.css("height", "auto");
                                    if ($.isPlainObject(params.selected) && params.selected.id) {
                                        var radio = container.find(':radio[value=' + params.selected.id + ']')
                                            .prop('checked', true),
                                            nostreegrid = table.data('nostreegrid');

                                        nostreegrid._view()._getSuperPanel().scrollChildIntoView(radio);
                                    }
                                },
                                columns: params.columns
                            });
                    };

                params.columns.unshift({
                    allowMoving : false,
                    allowSizing : false,
                    width : 35,
                    ensurePxWidth : true,
                    cellFormatter : function(args) {
                        if ($.isPlainObject(args.row.data)) {

                            $('<input type="radio" />').attr({
                                    name : params.input_name,
                                    value : args.row.data._id
                                })
                                .click(function() {
                                    // Save the selected in params in cas of a refresh tree event
                                    params.selected = {
                                        id : args.row.data._id,
                                        model : args.row.data._model
                                    };
                                    $(this).trigger('selectionChanged', args.row.data);
                                })
                                .appendTo(args.$container);

                            return true;
                        }
                    }
                });

                table.css({
                        height : '100%',
                        width : '100%'
                    });
                table.nosOnShow('one', init);
            });
        });
</script>
