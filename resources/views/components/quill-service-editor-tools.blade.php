@once
    <style>
        .smnote .ql-editor,
        .smnote .ql-editor p {
            font-size: 11pt !important;
            font-family: inherit;
        }

        .smnote.ql-container,
        .smnote .ql-container {
            min-height: 250px;
            max-height: 75vh;
            overflow: auto;
            resize: vertical;
        }

        .smnote.ql-container .ql-editor,
        .smnote .ql-container .ql-editor,
        .smnote .ql-editor {
            min-height: 250px;
            overflow-x: auto;
            overflow-y: auto;
        }

        .service-editor-table-tools {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            padding: 0.35rem;
            border: 1px solid #dee2e6;
            border-top: 0;
            background: #f8f9fa;
        }

        .smnote .ql-editor table {
            display: table;
            width: max-content;
            min-width: 100%;
            border-collapse: collapse;
            margin: 0.5rem 0;
            table-layout: auto;
        }

        .smnote .ql-editor tr {
            display: table-row;
        }

        .smnote .ql-editor th,
        .smnote .ql-editor td {
            display: table-cell;
            border: 1px solid #adb5bd;
            min-width: 130px;
            padding: 0.35rem 0.5rem;
            white-space: normal;
            word-break: normal;
            overflow-wrap: normal;
            vertical-align: top;
        }

        .smnote .ql-editor td:focus,
        .smnote .ql-editor th:focus {
            outline: 2px solid rgba(13, 110, 253, 0.25);
            outline-offset: -2px;
        }
    </style>

    <script>
        window.serviceEditorTableTools = window.serviceEditorTableTools || (function() {
            function notifyChange(onChange) {
                if (typeof onChange === 'function') {
                    onChange();
                }
            }

            function getEditorTableContext(quill) {
                const selection = window.getSelection();

                if (!selection || selection.rangeCount === 0) {
                    return {};
                }

                let node = selection.anchorNode;

                if (!node) {
                    return {};
                }

                if (node.nodeType === Node.TEXT_NODE) {
                    node = node.parentElement;
                }

                if (!node || !quill.root.contains(node)) {
                    return {};
                }

                return {
                    cell: node.closest('td, th'),
                    row: node.closest('tr'),
                    table: node.closest('table'),
                };
            }

            function createEditableTable(rows = 2, cols = 2) {
                const table = document.createElement('table');
                const tbody = document.createElement('tbody');

                for (let rowIndex = 0; rowIndex < rows; rowIndex++) {
                    const row = document.createElement('tr');

                    for (let colIndex = 0; colIndex < cols; colIndex++) {
                        const cell = document.createElement('td');
                        cell.innerHTML = '<br>';
                        row.appendChild(cell);
                    }

                    tbody.appendChild(row);
                }

                table.appendChild(tbody);
                return table;
            }

            function focusCell(cell) {
                if (!cell) {
                    return;
                }

                const range = document.createRange();
                range.selectNodeContents(cell);
                range.collapse(true);

                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
            }

            function createEmptyTableCell(tagName = 'td') {
                const cell = document.createElement(tagName.toLowerCase() === 'th' ? 'th' : 'td');
                cell.innerHTML = '<br>';
                return cell;
            }

            function getRowCellCount(row) {
                return Array.from(row.children).filter(child => ['TD', 'TH'].includes(child.tagName)).length;
            }

            function getTableMatrix(table) {
                const rows = Array.from(table.rows);
                const maxColumns = Math.max(1, ...rows.map(row => row.cells.length));

                return rows.map(row => {
                    const cells = Array.from(row.cells).map(cell => ({
                        tag: cell.tagName === 'TH' ? 'th' : 'td',
                        html: cell.innerHTML || '<br>',
                    }));

                    while (cells.length < maxColumns) {
                        cells.push({
                            tag: 'td',
                            html: '<br>',
                        });
                    }

                    return cells;
                });
            }

            function buildTableFromMatrix(matrix) {
                const table = document.createElement('table');
                const tbody = document.createElement('tbody');

                matrix.forEach(rowData => {
                    const row = document.createElement('tr');

                    rowData.forEach(cellData => {
                        const cell = createEmptyTableCell(cellData.tag);
                        cell.innerHTML = cellData.html || '<br>';
                        row.appendChild(cell);
                    });

                    tbody.appendChild(row);
                });

                table.appendChild(tbody);
                return table;
            }

            function replaceTableFromMatrix(oldTable, matrix, targetRowIndex, targetCellIndex) {
                const newTable = buildTableFromMatrix(matrix);
                oldTable.replaceWith(newTable);

                const targetRow = newTable.rows[Math.max(0, Math.min(targetRowIndex, newTable.rows.length - 1))];
                const targetCell = targetRow?.cells[Math.max(0, Math.min(targetCellIndex, (targetRow?.cells.length || 1) - 1))];
                focusCell(targetCell || newTable.querySelector('td, th'));
            }

            function insertTableInEditor(quill, onChange) {
                const table = createEditableTable();
                const spacer = document.createElement('p');
                spacer.innerHTML = '<br>';
                const selection = window.getSelection();

                if (selection && selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);

                    if (quill.root.contains(range.commonAncestorContainer)) {
                        range.deleteContents();
                        range.insertNode(spacer);
                        range.insertNode(table);
                    } else {
                        quill.root.appendChild(table);
                        quill.root.appendChild(spacer);
                    }
                } else {
                    quill.root.appendChild(table);
                    quill.root.appendChild(spacer);
                }

                focusCell(table.querySelector('td, th'));
                notifyChange(onChange);
            }

            function handleTableAction(action, quill, onChange) {
                const tableModule = quill.getModule('table');

                if (tableModule) {
                    try {
                        quill.focus();

                        if (action === 'insert-table') {
                            tableModule.insertTable(2, 2);
                        }

                        if (action === 'add-row') {
                            tableModule.insertRowBelow();
                        }

                        if (action === 'add-column') {
                            tableModule.insertColumnRight();
                        }

                        if (action === 'delete-row') {
                            tableModule.deleteRow();
                        }

                        if (action === 'delete-column') {
                            tableModule.deleteColumn();
                        }

                        if (action === 'delete-table') {
                            tableModule.deleteTable();
                        }

                        notifyChange(onChange);
                        return;
                    } catch (error) {
                        alert('Coloca el cursor dentro de una tabla para usar esta opción.');
                        return;
                    }
                }

                const context = getEditorTableContext(quill);

                if (action === 'insert-table') {
                    insertTableInEditor(quill, onChange);
                    return;
                }

                if (!context.table || !context.cell || !context.row) {
                    alert('Coloca el cursor dentro de una tabla para usar esta opción.');
                    return;
                }

                const tableRows = Array.from(context.table.rows);
                const currentCells = Array.from(context.row.cells);
                const rowIndex = Math.max(tableRows.indexOf(context.row), 0);
                const cellIndex = currentCells.indexOf(context.cell);
                let matrix = getTableMatrix(context.table);
                const columnCount = Math.max(matrix[0]?.length || getRowCellCount(context.row), 1);

                if (action === 'add-row') {
                    const cellTag = context.cell.tagName === 'TH' ? 'th' : 'td';
                    const newRow = Array.from({ length: columnCount }, () => ({
                        tag: cellTag,
                        html: '<br>',
                    }));
                    matrix.splice(rowIndex + 1, 0, newRow);
                    replaceTableFromMatrix(context.table, matrix, rowIndex + 1, Math.max(cellIndex, 0));
                }

                if (action === 'add-column') {
                    const insertIndex = Math.max(cellIndex, 0) + 1;

                    matrix = matrix.map(rowData => {
                        const reference = rowData[Math.max(cellIndex, 0)] || rowData[rowData.length - 1] || {
                            tag: 'td',
                        };
                        rowData.splice(insertIndex, 0, {
                            tag: reference.tag || 'td',
                            html: '<br>',
                        });
                        return rowData;
                    });

                    replaceTableFromMatrix(context.table, matrix, rowIndex, insertIndex);
                }

                if (action === 'delete-row') {
                    if (matrix.length <= 1) {
                        matrix = [Array.from({ length: columnCount }, (_, index) => ({
                            tag: matrix[0]?.[index]?.tag || 'td',
                            html: '<br>',
                        }))];
                        replaceTableFromMatrix(context.table, matrix, 0, Math.max(cellIndex, 0));
                    } else {
                        matrix.splice(rowIndex, 1);
                        replaceTableFromMatrix(context.table, matrix, Math.min(rowIndex, matrix.length - 1), Math.max(cellIndex, 0));
                    }
                }

                if (action === 'delete-column') {
                    if (columnCount <= 1) {
                        matrix = matrix.map(rowData => rowData.map(cellData => ({
                            tag: cellData.tag,
                            html: '<br>',
                        })));
                        replaceTableFromMatrix(context.table, matrix, rowIndex, 0);
                    } else {
                        const deleteIndex = Math.max(cellIndex, 0);
                        matrix = matrix.map(rowData => {
                            rowData.splice(deleteIndex, 1);
                            return rowData;
                        });
                        replaceTableFromMatrix(context.table, matrix, rowIndex, Math.min(deleteIndex, columnCount - 2));
                    }
                }

                if (action === 'delete-table') {
                    const fallback = document.createElement('p');
                    fallback.innerHTML = '<br>';
                    context.table.replaceWith(fallback);
                }

                notifyChange(onChange);
            }

            function add(quill, editorId, onChange) {
                if (!quill || !editorId || document.getElementById(`${editorId}-table-tools`)) {
                    return;
                }

                const tools = $(`
                    <div class="service-editor-table-tools" id="${editorId}-table-tools">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-table-action="insert-table">
                            <i class="bi bi-table"></i> Tabla
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-table-action="add-row">
                            <i class="bi bi-plus-lg"></i> Fila
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-table-action="add-column">
                            <i class="bi bi-plus-lg"></i> Columna
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" data-table-action="delete-row">
                            <i class="bi bi-dash-lg"></i> Fila
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" data-table-action="delete-column">
                            <i class="bi bi-dash-lg"></i> Columna
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" data-table-action="delete-table">
                            <i class="bi bi-trash"></i> Tabla
                        </button>
                    </div>
                `);

                tools.on('mousedown', function(event) {
                    event.preventDefault();
                });

                tools.on('click', 'button[data-table-action]', function() {
                    handleTableAction($(this).data('table-action'), quill, onChange);
                });

                $(quill.container).after(tools);
            }

            return {
                add,
            };
        })();

        window.addServiceEditorTableTools = function(quill, editorId, onChange) {
            window.serviceEditorTableTools.add(quill, editorId, onChange);
        };
    </script>
@endonce
