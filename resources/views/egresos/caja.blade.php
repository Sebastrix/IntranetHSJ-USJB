<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Caja - HC {{ $numhc ?: 'Sin HC' }}
    </title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: #eef2f7;
            color: #111;
            font-family: "Courier New", Courier, monospace;
        }

        .page {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 10px;
        }

        .page-header {
            max-width: 150mm;
            margin: 0 auto 10px auto;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 14px 16px;
            font-family: Arial, sans-serif;
        }

        .page-header h1 {
            margin: 0 0 8px;
            font-size: 24px;
            color: #0f2d72;
        }

        .page-header p {
            margin: 5px 0;
            font-size: 14px;
            color: #475569;
        }

        .empty {
            max-width: 150mm;
            margin: 0 auto;
            padding: 18px;
            background: #fff;
            border: 1px dashed #94a3b8;
            text-align: center;
            font-family: Arial, sans-serif;
            border-radius: 10px;
            color: #475569;
        }

        .actions {
            max-width: 150mm;
            margin: 0 auto 12px auto;
            display: flex;
            justify-content: flex-end;
        }

        .print-button {
            border: 0;
            border-radius: 8px;
            padding: 10px 18px;
            background: #2563eb;
            color: white;
            font-weight: bold;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }

        .voucher {
            width: 150mm;
            min-height: auto;
            margin: 0 auto 16px auto;
            background: #fff;
            border: 1px solid #222;
            padding: 10mm 9mm;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .hospital {
            text-align: center;
            line-height: 1.35;
        }

        .hospital h2 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 0.4px;
        }

        .hospital div {
            font-size: 12px;
        }

        .divider {
            margin: 10px 0;
            border-top: 1px dashed #222;
        }

        .voucher-title {
            text-align: center;
        }

        .voucher-title .label {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .voucher-title .number {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 0.8px;
        }

        .reprint-box {
            margin: 12px 0;
            border: 2px solid #222;
            padding: 10px 8px;
            text-align: center;
            line-height: 1.5;
        }

        .reprint-box .main {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .reprint-box .sub {
            font-size: 12px;
            font-weight: bold;
        }

        .meta {
            width: 100%;
            margin-top: 6px;
            font-size: 12px;
            line-height: 1.6;
        }

        .meta-row {
            display: grid;
            grid-template-columns: 32mm 1fr;
            column-gap: 4mm;
        }

        .meta-row strong {
            font-weight: bold;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 12px;
        }

        .detail-table thead th {
            border-bottom: 1px solid #222;
            border-top: 1px solid #222;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
        }

        .detail-table tbody td {
            padding: 6px 4px;
            vertical-align: top;
            border-bottom: 1px dashed #999;
        }

        .detail-table .qty {
            width: 12mm;
            text-align: left;
        }

        .detail-table .code {
            width: 24mm;
        }

        .detail-table .amount {
            width: 24mm;
            text-align: right;
            white-space: nowrap;
        }

        .total-box {
            margin-top: 12px;
            border-top: 2px solid #222;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 22px;
            font-weight: bold;
        }

        .footer-note {
            margin-top: 16px;
            padding-top: 8px;
            border-top: 1px dashed #222;
            text-align: center;
            font-size: 11px;
            line-height: 1.5;
        }

        @media print {
            body {
                background: white;
            }

            .page {
                padding: 0;
                max-width: none;
            }

            .page-header,
            .actions {
                display: none !important;
            }

            .voucher {
                width: 150mm;
                box-shadow: none;
                border: 1px solid #222;
                margin: 0 auto 10mm auto;
                page-break-after: always;
            }

            .voucher:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>

<body>

<div class="page">

    <section class="page-header">
        <h1>Caja / Boleta</h1>

        <p>
            Constancia N.°
            {{ str_pad((string) $constancia->numero, 4, '0', STR_PAD_LEFT) }}
            -{{ $constancia->anio }}
        </p>

        <p>
            Paciente:
            <strong>{{ $constancia->paciente ?: 'Sin paciente' }}</strong>
        </p>

        <p>
            Historia Clínica:
            <strong>{{ $numhc ?: 'Sin HC' }}</strong>
        </p>

        <p>
            {{ $comprobantes->count() }}
            comprobante(s) CONTADO encontrado(s)
        </p>
    </section>

    @if ($comprobantes->isEmpty())

        <div class="empty">
            No se encontraron comprobantes de Caja con forma de pago
            <strong>CONTADO</strong>
            para la Historia Clínica {{ $numhc }}.
        </div>

    @else

        <div class="actions">
            <button
                type="button"
                class="print-button"
                onclick="window.print()"
            >
                Imprimir
            </button>
        </div>

        @foreach ($comprobantes as $comprobante)

        <article class="voucher">

            <header class="hospital">
                <h2>HOSPITAL SAN JOSÉ DE CHINCHA</h2>
                <div>Unidad Ejecutora 401 Salud Chincha</div>
                <div>Av. Alva Maurtua N.° 600</div>
                <div>RUC 20410275768</div>
            </header>

            <div class="divider"></div>

            <section class="voucher-title">
                <div class="label">BOLETA</div>
                <div class="number">{{ $comprobante['numero'] ?? '—' }}</div>
            </section>

            <section class="reprint-box">
                <div class="main">*** VISTA DE CONSULTA ***</div>
                <div class="sub">INFORMACIÓN RECUPERADA DEL SISTEMA DE CAJA</div>
            </section>

            <div class="divider"></div>

            <section class="meta">
                <div class="meta-row">
                    <strong>Fecha</strong>
                    <span>{{ $comprobante['fecha'] ?? '—' }} {{ $comprobante['hora'] ?? '' }}</span>
                </div>

                <div class="meta-row">
                    <strong>Paciente</strong>
                    <span>{{ $comprobante['cliente'] ?? $constancia->paciente }}</span>
                </div>

                <div class="meta-row">
                    <strong>H. Clínica</strong>
                    <span>{{ $comprobante['historia_clinica'] ?? $numhc }}</span>
                </div>

                <div class="meta-row">
                    <strong>Documento</strong>
                    <span>{{ $constancia->doc_iden ?: '—' }}</span>
                </div>

                <div class="meta-row">
                    <strong>Grupo de Pago</strong>
                    <span>{{ $comprobante['grupo_pago'] ?? '—' }}</span>
                </div>

                <div class="meta-row">
                    <strong>Forma de Pago</strong>
                    <span>{{ $comprobante['forma_pago'] ?? '—' }}</span>
                </div>
            </section>

            <div class="divider"></div>

            <table class="detail-table">
                <thead>
                    <tr>
                        <th class="qty">Cant.</th>
                        <th class="code">Código corto</th>
                        <th>Descripción</th>
                        <th class="amount">Importe</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse (($comprobante['detalles'] ?? []) as $detalle)
                        <tr>
                            <td class="qty">{{ $detalle['cantidad'] ?? 0 }}</td>
                            <td class="code">{{ $detalle['codigo'] ?: '—' }}</td>
                            <td>{{ $detalle['descripcion'] ?: 'Sin descripción' }}</td>
                            <td class="amount">
                                S/ {{ number_format((float) ($detalle['total'] ?? 0), 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No se encontró el detalle del comprobante.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="total-box">
                <span>TOTAL S/</span>
                <span>{{ number_format((float) ($comprobante['total'] ?? 0), 2) }}</span>
            </div>

            <div class="footer-note">
                <div>ID Caja: {{ $comprobante['id_documento'] ?? '—' }}</div>
                <div>Consulta integrada desde Egresos · Hospital San José de Chincha</div>
            </div>

        </article>

        @endforeach

    @endif

</div>

</body>
</html>