@extends('layouts.master')

@section('content')
    <div class="loading hide">
        <div class="position-absolute top-50 start-50">
            <div class="spinner-border text-light" role="status"></div>
        </div>
    </div>
    <div class="container d-flex justify-content-center align-items-center py-3">
        <div class="form-holder col-md-8 bg-body rounded-3 shadow-lg m-4">
            <form id="atis-input" method="post" class="p-4">
                <input type="hidden" id="last-generated" value="">
                <h2 class="fw-bold">Redbeard's ATIS Generator</h2>
                <p class="fs-6">A simple to use tool for non VATSIM/IVAO/PilotEdge controllers to generate an ATIS in
                    text and spoken formats.</p>
                <div class="my-3">
                    <a href="#" class="btn btn-primary me-2" id="squawk-generator">
                        <i class="fas fa-random"></i> Squawk Code Generator</a>
                    {{-- <a href="#" class="btn btn-primary">PDC Generator</a> --}}
                </div>
                <div class="row">
                    <div class="col">
                        <label for="icao" class="form-label">1. icao</label>
                        <input type="text" maxlength="4" class="form-control" id="icao" name="icao" required>
                    </div>
                    <div class="col-auto">
                        <div><label for="list-runways" class="form-label">2. Get Runways</label></div>
                        <button type="button" role="button" class="btn btn-primary" id="list-runways">
                            List Runways
                        </button>
                    </div>
                    <div class="col">
                        <label for="ident" class="form-label">3. ident</label>
                        <select class="form-select" name="ident" id="ident" required>
                            <option value="a">A</option>
                            <option value="b">B</option>
                            <option value="c">C</option>
                            <option value="d">D</option>
                            <option value="e">E</option>
                            <option value="f">F</option>
                            <option value="g">G</option>
                            <option value="h">H</option>
                            <option value="i">I</option>
                            <option value="j">J</option>
                            <option value="k">K</option>
                            <option value="l">L</option>
                            <option value="m">M</option>
                            <option value="n">N</option>
                            <option value="o">O</option>
                            <option value="p">P</option>
                            <option value="q">Q</option>
                            <option value="r">R</option>
                            <option value="s">S</option>
                            <option value="t">T</option>
                            <option value="u">U</option>
                            <option value="v">V</option>
                            <option value="w">W</option>
                            <option value="x">X</option>
                            <option value="y">Y</option>
                            <option value="z">Z</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div>
                        <label class="form-label">4. Select Approaches (optional)</label>
                    </div>
                    <div class="col-md-6">
                        <input type="checkbox" class="form-check-input" id="ils" name="approaches[]">
                        <label class="form-check-label" for="ils">ILS Approaches</label>
                    </div>
                    <div class="col-md-6">
                        <input type="checkbox" class="form-check-input" id="visual" name="approaches[]">
                        <label class="form-check-label" for="visual">Visual Approaches</label>
                    </div>
                </div>
                <div class="mt-3">
                    <label for="remarks1" class="form-label">5. Remarks (optional but encouraged)</label>
                    <textarea class="form-control" id="remarks1" name="remarks1"></textarea>

                </div>
                <div class="mt-3 d-flex justify-content-center">
                    <button type="submit" role="submit" class="btn btn-primary w-100">Generate ATIS</button>
                </div>
                
                <div id="runway-output"></div>
            </form>
            <div id="atis-output"></div>
        </div>
    </div>
    <div class="modal fade" id="squawk-modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Squawk Generator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p>Click the button below to generate a random IFR squawk code.</p>
                    <p id="squawk-output" class="fs-3"></p>
                    <p>
                        <button type="button" class="btn btn-primary" id="generate-squawk">Generate Squawk</button>
                        <button type="button" class="btn btn-primary hide" id="copy-squawk">Copy To
                            Clipboard</button>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <footer class="bg-light py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md text-center text-md-left">
                    <p class="text-muted"> <i class="fas fa-plane"></i> Powered by <a
                            href="https://github.com/RedbeardTFL/ATIS_GENERATOR" target="_blank"
                            style="color: #1b95e0; border-bottom: 1px dotted #1b95e0;">Redbeard's ATIS Generator</a> </p>
                </div>
                <div class="col-md text-center text-md-right">
                    <p class="text-muted">
                        <i class="fas fa-code-branch"></i>
                        Version:
                        <span class="fw-bold">
                            {{ \Tremby\LaravelGitVersion\GitVersionHelper::getVersion() }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </footer>
@endsection
