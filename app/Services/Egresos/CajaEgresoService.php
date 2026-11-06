<?php

namespace App\Services\Egresos;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

final class CajaEgresoService
{
    public function comprobantesContado(string $numhc): Collection
    {
        $numhc = trim($numhc);

        if ($numhc === '') {
            return collect();
        }

        try {
            $documentos = DB::connection('caja')
                ->table('Cabecera_documento_MH as d')
                ->join('Historia_clinica as hc', 'hc.id_hc', '=', 'd.id_hc')
                ->join('Jerarquia_Forma_Pago_MH as fp', 'fp.cod_jerar_forma_pago', '=', 'd.cod_jerar_forma_pago')
                ->whereRaw('LTRIM(RTRIM(hc.cod_hc)) = ?', [$numhc])
                ->whereRaw("UPPER(LTRIM(RTRIM(fp.fp_padre))) = 'CONTADO'")
                ->where('d.estado_doc', 'S')
                ->orderByDesc('d.id_documento')
                ->get([
                    'd.id_documento',
                    'd.serie_documento',
                    'd.num_documento',
                    'd.cliente',
                    'd.fecha_actu',
                    'd.hora_actu',
                    'd.sub_total_doc',
                    'd.igv_doc',
                    'd.total_doc',
                    'd.estado_doc',
                    'fp.nom_forma_pago',
                    'fp.fp_padre',
                    'hc.cod_hc',
                ]);

            if ($documentos->isEmpty()) {
                return collect();
            }

            $detalles = DB::connection('caja')
                ->table('Detalle_documento_MH as dd')
                ->leftJoin('Precio_MH as p', 'p.cod_precio', '=', 'dd.cod_precio')
                ->leftJoin('Nomenclatura_caja_MH as n', 'n.cod_nomen_caja', '=', 'p.cod_nomen_caja')
                ->whereIn('dd.id_documento', $documentos->pluck('id_documento')->all())
                ->orderBy('dd.id_documento')
                ->get([
                    'dd.id_documento',
                    'dd.cantidad_detalle',
                    'dd.precio_detalle',
                    'dd.total_detalle',
                    'n.cod_nomen_caja',
                    'n.descripcion_nomen_tipo',
                    'n.nomen_caja',
                ])
                ->groupBy('id_documento');

            return $documentos->map(function ($documento) use ($detalles) {
                return [
                    'id_documento' => trim((string) $documento->id_documento),
                    'historia_clinica' => trim((string) $documento->cod_hc),
                    'serie' => trim((string) $documento->serie_documento),
                    'numero' => trim((string) $documento->num_documento),
                    'cliente' => trim((string) ($documento->cliente ?? '')),
                    'fecha' => trim((string) ($documento->fecha_actu ?? '')),
                    'hora' => trim((string) ($documento->hora_actu ?? '')),
                    'forma_pago' => trim((string) $documento->nom_forma_pago),
                    'grupo_pago' => trim((string) $documento->fp_padre),
                    'subtotal' => (float) ($documento->sub_total_doc ?? 0),
                    'igv' => (float) ($documento->igv_doc ?? 0),
                    'total' => (float) ($documento->total_doc ?? 0),

                    'detalles' => $detalles
                        ->get($documento->id_documento, collect())
                        ->map(function ($detalle) {
                            return [
                                'codigo' => trim((string) ($detalle->cod_nomen_caja ?? '')),
                                'descripcion' => trim((string) (
                                    $detalle->descripcion_nomen_tipo
                                    ?: $detalle->nomen_caja
                                    ?: 'SIN DESCRIPCIÓN'
                                )),
                                'cantidad' => (float) ($detalle->cantidad_detalle ?? 0),
                                'precio' => (float) ($detalle->precio_detalle ?? 0),
                                'total' => (float) ($detalle->total_detalle ?? 0),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })->values();
        } catch (Throwable $e) {
            report($e);

            return collect();
        }
    }
        public function contarComprobantesContadoPorHistorias(array $historias): Collection
    {
        $historias = collect($historias)
            ->map(fn ($hc) => trim((string) $hc))
            ->filter()
            ->unique()
            ->values();

        if ($historias->isEmpty()) {
            return collect();
        }

        try {
            return DB::connection('caja')
                ->table('Cabecera_documento_MH as d')
                ->join(
                    'Historia_clinica as hc',
                    'hc.id_hc',
                    '=',
                    'd.id_hc'
                )
                ->join(
                    'Jerarquia_Forma_Pago_MH as fp',
                    'fp.cod_jerar_forma_pago',
                    '=',
                    'd.cod_jerar_forma_pago'
                )
                ->whereIn(
                    DB::raw('LTRIM(RTRIM(hc.cod_hc))'),
                    $historias->all()
                )
                ->where('d.estado_doc', 'S')
                ->whereRaw(
                    "UPPER(LTRIM(RTRIM(fp.fp_padre))) = 'CONTADO'"
                )
                ->selectRaw(
                    'LTRIM(RTRIM(hc.cod_hc)) as numhc, COUNT(*) as total'
                )
                ->groupByRaw(
                    'LTRIM(RTRIM(hc.cod_hc))'
                )
                ->pluck('total', 'numhc')
                ->map(fn ($total) => (int) $total);
        } catch (Throwable $e) {
            report($e);

            return collect();
        }
    }
}