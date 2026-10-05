<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Certificate;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Instrument;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Message;
use App\Models\Payment;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the Baritone Music Academy application's database.
     */
    public function run(): void
    {
        // 1. Academy Users
        $password = Hash::make('password');

        $admin = User::firstOrCreate([
            'name' => 'Prof. Franz Liszt',
            'email' => 'admin@academy.test',
            'role' => 'admin',
            'password' => $password,
            'phone' => '+1 (800) 440-0001',
            'bio' => 'General Director & Dean of Baritone Music Academy. Concert virtuoso and conductor.',
            'is_active' => true,
        ]);

        $instructor1 = User::firstOrCreate([
            'name' => 'Clara Schumann',
            'email' => 'clara.schumann@academy.test',
            'role' => 'instructor',
            'password' => $password,
            'phone' => '+1 (800) 440-0002',
            'bio' => 'Distinguished Professor of Piano Repertoire & Chamber Art. Celebrated concert pianist.',
            'is_active' => true,
        ]);

        $instructor2 = User::firstOrCreate([
            'name' => 'Niccolò Paganini',
            'email' => 'niccolo.paganini@academy.test',
            'role' => 'instructor',
            'password' => $password,
            'phone' => '+1 (800) 440-0003',
            'bio' => 'Master of the Violin. Virtuoso soloist focusing on left-hand dexterity, harmonics, and bow technique.',
            'is_active' => true,
        ]);

        $instructor3 = User::firstOrCreate([
            'name' => 'Jimi Hendrix',
            'email' => 'jimi.hendrix@academy.test',
            'role' => 'instructor',
            'password' => $password,
            'phone' => '+1 (800) 440-0004',
            'bio' => 'Guitar faculty chair. Master of modal improvisation, chord voicing, and tone expression.',
            'is_active' => true,
        ]);

        $student1 = User::firstOrCreate([
            'name' => 'Ludwig van Beethoven',
            'email' => 'student1@academy.test',
            'role' => 'student',
            'password' => $password,
            'phone' => '+1 555-0101',
            'bio' => 'Passionate piano student studying late Romantic phrasing and harmonic voice leading.',
            'is_active' => true,
        ]);

        $student2 = User::firstOrCreate([
            'name' => 'Johann Sebastian',
            'email' => 'student2@academy.test',
            'role' => 'student',
            'password' => $password,
            'phone' => '+1 555-0102',
            'bio' => 'Violin apprentice polishing Partitas and polyphonic string fingerings.',
            'is_active' => true,
        ]);

        $student3 = User::firstOrCreate([
            'name' => 'Frederic Chopin',
            'email' => 'student3@academy.test',
            'role' => 'student',
            'password' => $password,
            'phone' => '+1 555-0103',
            'bio' => 'Studying fingerstyle chord progressions and classical guitar harmonics.',
            'is_active' => true,
        ]);

        $student4 = User::firstOrCreate([
            'name' => 'Amadeus Mozart',
            'email' => 'student4@academy.test',
            'role' => 'student',
            'password' => $password,
            'phone' => '+1 555-0104',
            'bio' => 'New student eager to explore opera vocal breath control.',
            'is_active' => true,
        ]);

        // 2. Instruments / Disciplines
        $piano = Instrument::create([
            'name' => 'Concert Piano & Keys',
            'slug' => 'piano',
            'icon' => 'music-note-beamed',
            'description' => 'Classical grand piano, polyphony, touch mechanics, and sight-reading.',
        ]);

        $violin = Instrument::create([
            'name' => 'Violin & Strings',
            'slug' => 'violin',
            'icon' => 'music-player',
            'description' => 'Bowing mechanics, intonation, vibrato, shifting, and solo repertoire.',
        ]);

        $guitar = Instrument::create([
            'name' => 'Classical & Acoustic Guitar',
            'slug' => 'guitar',
            'icon' => 'soundwave',
            'description' => 'Fingerstyle technique, flamenco rasgueados, harmony, and fretboard mastery.',
        ]);

        $vocal = Instrument::create([
            'name' => 'Vocal Mastery & Bel Canto',
            'slug' => 'vocal',
            'icon' => 'mic',
            'description' => 'Breath support, resonance placement, opera coloratura, and diction.',
        ]);

        $drums = Instrument::create([
            'name' => 'Orchestral Percussion & Drums',
            'slug' => 'percussion',
            'icon' => 'disc',
            'description' => 'Poly-meter subdivision, snare rudiments, dynamic control, and groove.',
        ]);

        // 3. Courses
        $coursePiano = Course::create([
            'instrument_id' => $piano->id,
            'instructor_id' => $instructor1->id,
            'title' => 'Chopin Nocturnes: Phrasing, Touch & Rubato Mastery',
            'slug' => 'chopin-nocturnes-phrasing-touch-rubato',
            'level' => 'intermediate',
            'short_description' => 'Unlock the lyrical poetry and nuanced pedal artistry of Frédéric Chopin’s timeless Nocturnes.',
            'description' => "This masterclass explores the subtle balance between rhythmic liberty and pulse that characterizes authentic Romantic rubato. Through detailed measure-by-measure analyses of the famous Op. 9 No. 2 in E-flat Major, Op. 27 No. 2, and Op. 48 No. 1, you will learn how to produce a singing cantabile tone, execute intricate fiorituras with lightness, and balance complex left-hand chordal beds against the singing soprano line.",
            'cover_image' => 'images/courses/piano.svg',
            'fee' => 180.00,
            'duration_weeks' => 8,
            'status' => 'published',
        ]);

        $courseViolin = Course::create([
            'instrument_id' => $violin->id,
            'instructor_id' => $instructor2->id,
            'title' => 'Bach Solo Sonatas & Partitas: Polyphonic Bowing',
            'slug' => 'bach-solo-sonatas-partitas-polyphonic-bowing',
            'level' => 'advanced',
            'short_description' => 'Conquer the monumental unaccompanied works of J.S. Bach with baroque articulation and pure intonation.',
            'description' => "Unaccompanied violin literature reaches its historical zenith with the Sonatas and Partitas of J.S. Bach. In this course, maestro Paganini guides you through the architecture of the Chaconne in D Minor, the fugal voicing of the G minor Sonata, and the dance rhythms of the E Major Partita. Master 3- and 4-string chord rolling, string-crossing ergonomics, and historically informed phrasing.",
            'cover_image' => 'images/courses/violin.svg',
            'fee' => 220.00,
            'duration_weeks' => 10,
            'status' => 'published',
        ]);

        $courseGuitar = Course::create([
            'instrument_id' => $guitar->id,
            'instructor_id' => $instructor3->id,
            'title' => 'Fingerstyle Acoustic: Voice-Leading & Chord Chemistry',
            'slug' => 'fingerstyle-acoustic-voice-leading',
            'level' => 'beginner',
            'short_description' => 'Build a solo orchestra in your two hands through modern fingerpicking, percussive slaps, and voice leading.',
            'description' => "Learn how to simultaneously support a moving bassline, rich internal chord harmonies, and a soaring melody on a single acoustic guitar. We break down thumb independence, alternating bass patterns, right-hand nail care, natural harmonics, and open-tuning explorations in DADGAD.",
            'cover_image' => 'images/courses/guitar.svg',
            'fee' => 0.00, // Free course demo
            'duration_weeks' => 4,
            'status' => 'published',
        ]);

        $courseVocal = Course::create([
            'instrument_id' => $vocal->id,
            'instructor_id' => $instructor1->id,
            'title' => 'Bel Canto Foundations: Appoggio & Passaggio Technique',
            'slug' => 'bel-canto-foundations-appoggio-passaggio',
            'level' => 'intermediate',
            'short_description' => 'Cultivate effortless vocal power and seamless register transitions through historic Italian methodology.',
            'description' => "Discover the timeless principles of the Italian Bel Canto school: breath management (appoggio), pharyngeal resonance, open throat posture, and vowel modification across the vocal bridge (passaggio). Ideal for both classical opera and modern contemporary singers seeking endurance and agility.",
            'cover_image' => 'images/courses/vocal.svg',
            'fee' => 140.00,
            'duration_weeks' => 6,
            'status' => 'published',
        ]);

        // 4. Lessons for Chopin Course
        $pianoLessons = [
            [
                'title' => 'The Bel Canto Touch: Producing a Singing Tone',
                'position' => 1,
                'duration_minutes' => 18,
                'summary' => 'Arm weight release, finger cushioning, and horizontal legato.',
                'content' => "Welcome to Lesson 1 of our Chopin masterclass. Chopin famously advised his students to listen to Italian opera singers to understand piano phrasing.

Key Practice Steps:
1. Weight Transfer: Do not strike the keys from above. Rest your finger pads on the key surfaces and press through using the natural weight of your forearm.
2. Inward Wrist Motion: Maintain a supple wrist that gently circles on phrase climaxes to avoid percussive harshness.
3. Left-Hand Foundation: Practice the accompaniment alone, ensuring the bass root on beat 1 sounds resonant without drowning out the subsequent two offbeat chords.",
                'video_url' => 'https://www.youtube.com/watch?v=9E6b3swbnWg', // Chopin Nocturne Op. 9 No. 2 recording
                'is_preview' => true,
            ],
            [
                'title' => 'Anatomy of Rubato: Left Hand as Conductor, Right Hand as Poet',
                'position' => 2,
                'duration_minutes' => 22,
                'summary' => 'Metronomic balance in the left hand while the right hand declaims freely.',
                'content' => "Chopin's own description of rubato was: 'The left hand is the choirmaster; it must not waver or bend. Do with the right hand what you will and can.'

In this lesson we isolate measures 5 through 8:
- Step 1: Set your metronome at dotted quarter note = 48.
- Step 2: Play the left hand accompaniment until it flows effortlessly like a gentle Venetian barcarolle.
- Step 3: Introduce the right-hand fiorituras. Stretch slightly into the peak note, and gently speed up the descending chromatic cascade to return strictly on the next downbeat.",
                'video_url' => 'https://www.youtube.com/watch?v=wygy721nzRc',
                'is_preview' => false,
            ],
            [
                'title' => 'Fiorituras & Delicate Cadenzas: 11-lets, 22-lets, and Beyond',
                'position' => 3,
                'duration_minutes' => 25,
                'summary' => 'Executing irregular tuplets with pearl-like clarity and zero finger tension.',
                'content' => "Chopin's decorative tuplets (such as the 11-tuplet in m. 16 and 22-tuplet in m. 24) must never sound calculated or mathematically rigid. They should flutter like wind through silk.

Practice Protocol:
- Do not attempt to subdivide 11 against 3 in your head. Group the 11 notes into 4 + 4 + 3 as a temporary scaffolding, then immediately erase the accents.
- Play with flat finger pads rather than arched tips to achieve the 'jeu perlé' timbre.
- Release all thumb tension before the high note apex.",
                'video_url' => 'https://www.youtube.com/watch?v=9E6b3swbnWg',
                'is_preview' => false,
            ],
            [
                'title' => 'Pedaling Artistry: Syncopated, Flutter, and Half-Pedal Nuances',
                'position' => 4,
                'duration_minutes' => 20,
                'summary' => 'Avoiding harmonic blur while creating lush acoustic resonance.',
                'content' => "The damper pedal is the lungs of the piano. In Chopin, improper pedaling destroys delicate polyphony and turns subtle harmonic shifts into mud.

Techniques Covered:
1. Syncopated Pedaling: Depress the pedal *immediately after* the key strikes the bottom of its keybed, not simultaneously.
2. Half-pedaling on descending scales to clear high-register friction while retaining the deep bass note resonance.
3. Using the Una Corda (soft pedal) for timbre variation rather than merely for playing softly.",
                'video_url' => 'https://www.youtube.com/watch?v=wygy721nzRc',
                'is_preview' => false,
            ],
        ];

        foreach ($pianoLessons as $lData) {
            $coursePiano->lessons()->create($lData);
        }

        // 5. Lessons for Guitar Course
        $guitarLessons = [
            [
                'title' => 'Thumb Independence & Alternating Bass (Travis Picking)',
                'position' => 1,
                'duration_minutes' => 15,
                'summary' => 'Locking the thumb on bass strings 6, 5, and 4 while fingers pick melodies.',
                'content' => "Mastering fingerstyle begins with separating your brain's control of the thumb (p) from index (i), middle (m), and ring (a) fingers.

Exercise 1:
- Rest your thumb on the 6th string (E) and index on the 3rd string (G).
- Strike in steady quarter notes: 6 - 3 - 6 - 3.
- Do not allow the hand to bounce away from the top plate.",
                'video_url' => 'https://www.youtube.com/watch?v=2rre9779_aU',
                'is_preview' => true,
            ],
            [
                'title' => 'Natural Harmonics & Percussive Top-Plate Slaps',
                'position' => 2,
                'duration_minutes' => 18,
                'summary' => 'Chime nodes at the 12th, 7th, and 5th frets paired with wrist thumps.',
                'content' => "Learn how modern fingerstyle legends like Tommy Emmanuel and Michael Hedges create rhythmic propulsion. We cover fretboard nodes and acoustic bass kick techniques.",
                'video_url' => 'https://www.youtube.com/watch?v=2rre9779_aU',
                'is_preview' => false,
            ],
        ];

        foreach ($guitarLessons as $lData) {
            $courseGuitar->lessons()->create($lData);
        }

        // 6. Quizzes
        $quizPiano = $coursePiano->quizzes()->create([
            'title' => 'Chopin Harmonic Form & Romantic Phrasing Exam',
            'description' => 'Test your analytical comprehension of harmonic cadences, rubato mechanics, and ornamentation in Chopin Nocturnes.',
            'pass_mark' => 75,
            'time_limit_minutes' => 15,
            'is_required' => true,
            'is_published' => true,
        ]);

        $q1 = $quizPiano->questions()->create([
            'text' => 'According to Chopin’s own teaching philosophy, what is the primary role of the left hand during rubato?',
            'position' => 1,
            'points' => 25,
        ]);
        $q1->options()->create(['text' => 'It must strictly maintain the rhythmic pulse like a choirmaster or conductor', 'is_correct' => true]);
        $q1->options()->create(['text' => 'It accelerates and decelerates in exact synchronization with the right hand melody', 'is_correct' => false]);
        $q1->options()->create(['text' => 'It should play as loudly as possible to mask rhythmic discrepancies', 'is_correct' => false]);
        $q1->options()->create(['text' => 'It should be played staccato throughout every measure', 'is_correct' => false]);

        $q2 = $quizPiano->questions()->create([
            'text' => 'What is the correct execution method for syncopated (legato) pedaling on the concert grand?',
            'position' => 2,
            'points' => 25,
        ]);
        $q2->options()->create(['text' => 'Depress the pedal immediately before striking the new key', 'is_correct' => false]);
        $q2->options()->create(['text' => 'Release and instantly re-depress the pedal right after the new harmony sounds', 'is_correct' => true]);
        $q2->options()->create(['text' => 'Hold the damper pedal down continuously throughout the entire piece', 'is_correct' => false]);
        $q2->options()->create(['text' => 'Use only the soft (una corda) pedal without the damper pedal', 'is_correct' => false]);

        $q3 = $quizPiano->questions()->create([
            'text' => 'How should irregular fiorituras (such as an 11-tuplet over a 3-beat accompaniment) be conceived musically?',
            'position' => 3,
            'points' => 25,
        ]);
        $q3->options()->create(['text' => 'As mathematically rigid subdivisions calculated with a metronome', 'is_correct' => false]);
        $q3->options()->create(['text' => 'As an expressive vocal cascade that floats flexibly above the steady left-hand harmony', 'is_correct' => true]);
        $q3->options()->create(['text' => 'By skipping notes so that the count lands on an even number', 'is_correct' => false]);
        $q3->options()->create(['text' => 'By playing every note with heavy accented staccato', 'is_correct' => false]);

        $q4 = $quizPiano->questions()->create([
            'text' => 'In Nocturne in E-flat Major (Op. 9 No. 2), what meter and genre characteristics underpin the left hand accompaniment?',
            'position' => 4,
            'points' => 25,
        ]);
        $q4->options()->create(['text' => 'A fast 2/4 military march with sharp downbeats', 'is_correct' => false]);
        $q4->options()->create(['text' => 'A lilting 12/8 Venetian barcarolle / vocal bel canto romance', 'is_correct' => true]);
        $q4->options()->create(['text' => 'A 3/4 rapid Viennese waltz tempo', 'is_correct' => false]);
        $q4->options()->create(['text' => 'A syncopated 4/4 ragtime bass pattern', 'is_correct' => false]);

        // 7. Assignments
        $asgPiano = $coursePiano->assignments()->create([
            'lesson_id' => $coursePiano->lessons()->first()->id,
            'title' => 'Etude Recording: Nocturne Op. 9 No. 2 (Measures 1–8)',
            'instructions' => "Record an audio or video take of the opening theme (measures 1 through 8) of Chopin's Nocturne in E-flat Major, Op. 9 No. 2.

Focus points for evaluation:
1. Pure cantabile tone on the right-hand melody without harsh accents.
2. Controlled arm weight and delicate, unhurried left-hand chord beds.
3. Subtle syncopated pedaling without chord smearing.

Upload your performance in MP3, WAV, or MP4 format.",
            'due_at' => now()->addDays(14),
            'max_score' => 100,
        ]);

        // 8. Enrollments & Progress
        // Student 1 (Beethoven) completed the Chopin course!
        $enr1 = Enrollment::create([
            'user_id' => $student1->id,
            'course_id' => $coursePiano->id,
            'status' => 'completed',
            'progress' => 100,
            'enrolled_at' => now()->subDays(30),
            'completed_at' => now()->subDays(2),
        ]);

        // Mark all lessons completed for Student 1
        foreach ($coursePiano->lessons as $l) {
            LessonProgress::create([
                'user_id' => $student1->id,
                'lesson_id' => $l->id,
                'completed_at' => now()->subDays(5),
            ]);
        }

        // Passed Quiz Attempt for Student 1
        QuizAttempt::create([
            'quiz_id' => $quizPiano->id,
            'user_id' => $student1->id,
            'score' => 100.0,
            'passed' => true,
            'answers' => [
                $q1->id => $q1->options->firstWhere('is_correct', true)->id,
                $q2->id => $q2->options->firstWhere('is_correct', true)->id,
                $q3->id => $q3->options->firstWhere('is_correct', true)->id,
                $q4->id => $q4->options->firstWhere('is_correct', true)->id,
            ],
            'completed_at' => now()->subDays(3),
        ]);

        // Submission with Instructor Feedback for Student 1
        Submission::create([
            'assignment_id' => $asgPiano->id,
            'user_id' => $student1->id,
            'file_path' => 'demo/chopin-nocturne-take1.mp3',
            'original_name' => 'chopin_op9no2_beethoven_take.mp3',
            'notes' => 'Attempted to emphasize the singing soprano voice while keeping the bass notes sustained warmly.',
            'score' => 96,
            'feedback' => 'Exceptional cantabile expression and poised rubato in mm. 4-6! The fioritura cascaded with genuine delicacy. Watch the release on the third beat of measure 7 so the harmonic breath is completely clean before the cadence. Brava!',
            'graded_at' => now()->subDays(4),
            'graded_by' => $instructor1->id,
        ]);

        // Certificate for Student 1
        Certificate::create([
            'enrollment_id' => $enr1->id,
            'code' => 'HMA-7842-KL91-P209',
            'issued_at' => now()->subDays(2),
        ]);

        // Paid payment for Student 1
        Payment::create([
            'enrollment_id' => $enr1->id,
            'amount' => 180.00,
            'method' => 'bank_transfer',
            'reference' => 'WIRE-CHOPIN-001',
            'status' => 'paid',
            'paid_at' => now()->subDays(30),
            'recorded_by' => $admin->id,
            'notes' => 'Tuition paid in full via European wire transfer.',
        ]);

        // Student 2 enrolled in Violin course (Active)
        $enr2 = Enrollment::create([
            'user_id' => $student2->id,
            'course_id' => $courseViolin->id,
            'status' => 'active',
            'progress' => 35,
            'enrolled_at' => now()->subDays(12),
        ]);

        Payment::create([
            'enrollment_id' => $enr2->id,
            'amount' => 220.00,
            'method' => 'card',
            'reference' => 'STRIPE-BACH-8841',
            'status' => 'paid',
            'paid_at' => now()->subDays(12),
            'recorded_by' => $admin->id,
        ]);

        // Student 3 enrolled in Free Guitar course
        Enrollment::create([
            'user_id' => $student3->id,
            'course_id' => $courseGuitar->id,
            'status' => 'active',
            'progress' => 50,
            'enrolled_at' => now()->subDays(8),
        ]);

        // Student 4 enrolled in Vocal course with Pending Payment
        $enr4 = Enrollment::create([
            'user_id' => $student4->id,
            'course_id' => $courseVocal->id,
            'status' => 'pending',
            'progress' => 0,
            'enrolled_at' => now()->subDays(1),
        ]);

        Payment::create([
            'enrollment_id' => $enr4->id,
            'amount' => 140.00,
            'method' => 'bank_transfer',
            'reference' => 'REF-VOCAL-9921',
            'status' => 'pending',
            'notes' => 'Wire transfer sent yesterday morning from Austrian Volksbank.',
        ]);

        // 9. Class Sessions / Rehearsals (for FullCalendar)
        ClassSession::create([
            'course_id' => $coursePiano->id,
            'title' => 'Chopin Masterclass & Phrasing Sectional',
            'description' => 'Live performance critique of Nocturne etudes. Each student receives 15 minutes of open masterclass coaching.',
            'starts_at' => now()->addDays(2)->setHour(14)->setMinute(0),
            'ends_at' => now()->addDays(2)->setHour(16)->setMinute(0),
            'location' => 'Grand Concert Hall & Studio 1',
            'meeting_url' => 'https://meet.google.com/xyz-piano-baritone',
        ]);

        ClassSession::create([
            'course_id' => $courseViolin->id,
            'title' => 'Bach Polyphonic Bowing Clinic',
            'description' => 'Ergonomics of multi-string chord splitting and baroque bow weight distribution.',
            'starts_at' => now()->addDays(4)->setHour(17)->setMinute(30),
            'ends_at' => now()->addDays(4)->setHour(19)->setMinute(0),
            'location' => 'Chamber Music Salon 3',
            'meeting_url' => 'https://zoom.us/j/9928172648',
        ]);

        ClassSession::create([
            'course_id' => $courseGuitar->id,
            'title' => 'Fingerstyle Harmony Workshop',
            'description' => 'Alternating bass syncopation and natural harmonics clinic.',
            'starts_at' => now()->addDays(6)->setHour(11)->setMinute(0),
            'ends_at' => now()->addDays(6)->setHour(12)->setMinute(30),
            'location' => 'Acoustic Studio B',
            'meeting_url' => 'https://meet.google.com/abc-guitar-baritone',
        ]);

        // 10. Academy Bulletins & Announcements
        Announcement::create([
            'user_id' => $admin->id,
            'title' => 'Welcome to the New Conservatory Academic Term',
            'body' => "We are thrilled to welcome all new and returning virtuosos to Baritone Music Academy! Our studios are fully equipped for high-definition recording critique, music theory assessments, and digital diploma conferral. Please review your class calendar for upcoming live masterclasses.",
            'audience' => 'all',
            'is_pinned' => true,
        ]);

        Announcement::create([
            'user_id' => $instructor1->id,
            'course_id' => $coursePiano->id,
            'title' => 'Scores & Urtext Editions for Nocturne Op. 9 No. 2 Uploaded',
            'body' => "Dear pianists, please download the newly annotated Henle Urtext PDF score attached to Lesson 1 before our live rehearsal this Thursday. Pay special attention to the dynamic markings in the coda.",
            'audience' => 'students',
            'is_pinned' => false,
        ]);

        // 11. Private Messages
        $msg1 = Message::create([
            'sender_id' => $student1->id,
            'recipient_id' => $instructor1->id,
            'subject' => 'Question regarding Chopin Nocturne Op. 9 No. 2 Coda Pedaling',
            'body' => "Dear Professor Schumann, I have been practicing the delicate pianissimo arpeggios in the closing coda. Should I employ the una corda pedal throughout the final four measures, or rely solely on finger touch to achieve the ethereal pianissimo color?",
        ]);

        $msg1->replies()->create([
            'sender_id' => $instructor1->id,
            'recipient_id' => $student1->id,
            'subject' => 'Re: Question regarding Chopin Nocturne Op. 9 No. 2 Coda Pedaling',
            'body' => "Dear Ludwig, a wonderful musical inquiry! I recommend depressing the una corda pedal at the start of the 'dolcissimo' indication, but ensure you maintain deep arm cushion on the bass pedal notes so the resonance does not suddenly thin out. We shall work on this in our upcoming live session!",
        ]);

        // 12. Blog & Journal Publications
        $this->call(BlogPostSeeder::class);
    }
}
