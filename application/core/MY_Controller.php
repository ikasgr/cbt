<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    /**
     * @param $data
     * @param $strict
     * @return bool
     *
     * https://developer.wordpress.org/reference/functions/is_serialized/#Source_File
     */
    private function is_serialized( $data, $strict = true ) {
        // If it isn't a string, it isn't serialized.
        if ( ! is_string( $data ) ) {
            return false;
        }
        $data = trim( $data );
        if ( 'N;' === $data ) {
            return true;
        }
        if ( strlen( $data ) < 4 ) {
            return false;
        }
        if ( ':' !== $data[1] ) {
            return false;
        }
        if ( $strict ) {
            $lastc = substr( $data, -1 );
            if ( ';' !== $lastc && '}' !== $lastc ) {
                return false;
            }
        } else {
            $semicolon = strpos( $data, ';' );
            $brace     = strpos( $data, '}' );
            // Either ; or } must exist.
            if ( false === $semicolon && false === $brace ) {
                return false;
            }
            // But neither must be in the first X characters.
            if ( false !== $semicolon && $semicolon < 3 ) {
                return false;
            }
            if ( false !== $brace && $brace < 4 ) {
                return false;
            }
        }
        $token = $data[0];
        switch ( $token ) {
            case 's':
                if ( $strict ) {
                    if ( '"' !== $data[strlen($data) - 2]) {
                        return false;
                    }
                } elseif ( false === strpos( $data, '"' ) ) {
                    return false;
                    //} elseif ( ! str_contains( $data, '"' ) ) {
                    //    return false;
                }
            // Or else fall through.
            case 'a':
            case 'O':
            case 'E':
                return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool) preg_match( "/^{$token}:[0-9.E+-]+;$end/", $data );
        }
        return false;
    }

    /**
     * @param $data
     * @return mixed
     *
     * https://developer.wordpress.org/reference/functions/maybe_unserialize/
     */
    protected function maybe_unserialize($data ) {
        // Don't attempt to unserialize data that wasn't serialized going in.
        if ( $this->is_serialized( $data ) ) {
            return @unserialize( trim( $data ) );
        }

        return $data;
    }

    /**
     * @param $jawabans
     * @param bool $withIdSiswa
     * @return array
     */
    protected function unserializeJawabanSiswa($jawabans, $withIdSiswa = true)
    {
        $jawabans_siswa = [];
        $soal = [];
        foreach ($jawabans as $jawaban_siswa) {
            if ($jawaban_siswa->jenis_soal == '2') {
                $jawaban_siswa->opsi_a = $this->maybe_unserialize($jawaban_siswa->opsi_a ?? '');
                $jawaban_siswa->jawaban_siswa = $this->maybe_unserialize($jawaban_siswa->jawaban_siswa ?? '');
                $jawaban_siswa->jawaban_benar = $this->maybe_unserialize($jawaban_siswa->jawaban_benar ?? '');
                $jawaban_siswa->jawaban = $this->maybe_unserialize($jawaban_siswa->jawaban ?? '');

                $jawaban_siswa->jawaban_benar = array_map([$this, 'arrToUpper'], $jawaban_siswa->jawaban_benar);
                $jawaban_siswa->jawaban_benar = array_filter($jawaban_siswa->jawaban_benar, 'strlen');

                $jawaban_siswa->jawaban = array_map([$this, 'arrToUpper'], $jawaban_siswa->jawaban);
                $jawaban_siswa->jawaban = array_filter($jawaban_siswa->jawaban, 'strlen');
            }

            if ($jawaban_siswa->jenis_soal == '3') {
                $jawaban_siswa->jawaban_siswa = $this->maybe_unserialize($jawaban_siswa->jawaban_siswa ?? '');
                $jawaban_siswa->jawaban_benar = $this->maybe_unserialize($jawaban_siswa->jawaban_benar ?? '');
                $jawaban_siswa->jawaban = $this->maybe_unserialize($jawaban_siswa->jawaban ?? '');

                $jawaban_siswa->jawaban_siswa = json_decode(json_encode($jawaban_siswa->jawaban_siswa));
                $jawaban_siswa->jawaban_benar = json_decode(json_encode($jawaban_siswa->jawaban_benar));
                $jawaban_siswa->jawaban = json_decode(json_encode($jawaban_siswa->jawaban));

                $arrAlphabet = range('A', 'Z');
                if (!isset($jawaban_siswa->jawaban_siswa) || !isset($jawaban_siswa->jawaban_siswa->links)) {
                    $arrjwbnSiswa = [];
                    if ($jawaban_siswa->jawaban_siswa) {
                        foreach ($jawaban_siswa->jawaban_siswa->jawaban as $idx => $jbs) {
                            if ($idx > 0) {
                                $arrjwbnSiswa[$idx] = [];
                                foreach ($jbs as $idxs => $jb) {
                                    if ($idxs > 0) {
                                        if ($jb === '1') {
                                            $arrjwbnSiswa[$idx][] = $arrAlphabet[$idxs - 1];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($jawaban_siswa->jawaban_siswa) {
                        $jawaban_siswa->jawaban_siswa->links = json_decode(json_encode($arrjwbnSiswa));
                    } else {
                        $jawaban_siswa->jawaban_siswa = ['links' => $arrjwbnSiswa];
                        $jawaban_siswa->jawaban_siswa = json_decode(json_encode($jawaban_siswa->jawaban_siswa));
                    }
                }

                $arrjwbn = [];
                foreach ($jawaban_siswa->jawaban_benar->jawaban as $idx => $jbs) {
                    if ($idx > 0) {
                        $arrjwbn[$idx] = [];
                        foreach ($jbs as $idxs => $jb) {
                            if ($idxs > 0) {
                                if ($jb === '1') {
                                    $arrjwbn[$idx][] = $arrAlphabet[$idxs - 1];
                                }
                            }
                        }
                    }
                }
                $jawaban_siswa->jawaban_benar->links = json_decode(json_encode($arrjwbn));
            }
            if ($withIdSiswa) $jawabans_siswa[$jawaban_siswa->id_siswa][$jawaban_siswa->jenis_soal][] = $jawaban_siswa;
            else $jawabans_siswa[$jawaban_siswa->jenis_soal][] = $jawaban_siswa;

            $soal[$jawaban_siswa->jenis_soal][] = $jawaban_siswa;
        }
        return [
            'jawaban'   => $jawabans_siswa,
            'soal'      => $soal
        ];
    }
}
