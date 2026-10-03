<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    /** Student's own certificates. */
    public function index()
    {
        $certificates = Certificate::whereHas('enrollment', fn ($q) => $q->where('user_id', auth()->id()))
            ->with('enrollment.course.instrument', 'enrollment.course.instructor')
            ->latest('issued_at')
            ->get();

        return view('student.certificates', compact('certificates'));
    }

    /** Public verification form / lookup. */
    public function verify(Request $request)
    {
        $certificate = null;
        if ($request->filled('code')) {
            $certificate = Certificate::where('code', strtoupper(trim($request->code)))
                ->with('enrollment.user', 'enrollment.course.instructor')
                ->first();
        }

        return view('public.certificates.verify', [
            'certificate' => $certificate,
            'searched' => $request->filled('code'),
        ]);
    }

    public function show(Certificate $certificate)
    {
        $certificate->load('enrollment.user', 'enrollment.course.instructor', 'enrollment.course.instrument');

        return view('certificates.show', compact('certificate'));
    }

    public function download(Certificate $certificate)
    {
        $user = auth()->user();
        $certificate->load('enrollment.user', 'enrollment.course.instructor', 'enrollment.course.instrument');

        abort_unless(
            $user->isAdmin() || $certificate->enrollment->user_id === $user->id
                || $certificate->enrollment->course->instructor_id === $user->id,
            403
        );

        return Pdf::loadView('certificates.pdf', compact('certificate'))
            ->setPaper('a4', 'landscape')
            ->download('certificate-'.$certificate->code.'.pdf');
    }
}
