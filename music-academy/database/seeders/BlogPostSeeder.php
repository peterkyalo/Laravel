<?php

namespace Database\Seeders;

use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $faculty = User::whereIn('role', ['instructor', 'admin'])->get();
        if ($faculty->isEmpty()) {
            return;
        }

        $franz = $faculty->firstWhere('email', 'admin@academy.test') ?? $faculty->first();
        $clara = $faculty->firstWhere('email', 'clara.schumann@academy.test') ?? $faculty->first();
        $paganini = $faculty->firstWhere('email', 'niccolo.paganini@academy.test') ?? $faculty->first();

        $student = User::where('role', 'student')->first();

        $posts = [
            [
                'title' => 'Mastering Polyphonic Intonation in Bach\'s Chaconne in D Minor',
                'category' => 'Masterclass & Technique',
                'tags' => 'Bach, Polyphony, Violin, Chaconne, Baroque, Intonation',
                'author_id' => $paganini->id,
                'cover_image' => 'images/courses/violin.svg',
                'read_time_minutes' => 7,
                'excerpt' => 'A definitive pedagogical guide to bow weight distribution, thumb freedom, and vocal polyphony over four strings.',
                'body' => '<h3>The Architectural Majesty of the Ciaccona</h3><p>When performing the culminating monument of J.S. Bach\'s <em>Partita No. 2 in D minor (BWV 1004)</em>, the violinist ceases to be a single melodic line and transforms into a full pipe organ. The central challenge of the opening four-measure chaconne progression lies not in finger agility, but in <strong>sustained resonance</strong>.</p><h4>1. The Secret to Three- and Four-Note Arpeggiated Chords</h4><p>Never break the chord with percussive downward violence. Instead, approach the lower two strings as an impulse, rolling smoothly onto the upper voices where the harmonic resolution resides. Maintain the thumb loosely balanced against the violin neck; tension here paralyzes the left-hand fifth finger during wide tenths.</p><blockquote>"Bach requires that the violin sing with choral breath. Do not force the sound with right-hand tension; let the bow hair speak through speed and release."</blockquote><h4>2. Maintaining Intonation in Thirty-Two Variations</h4><p>Because the work stays firmly rooted in D minor before modulating to the radiant D major center, the player must rigorously tune to pure harmonic intervals. Practice the arpeggiated variations with a drone on open D, checking every fifth and third against open strings.</p>',
            ],
            [
                'title' => 'The Art of Cantabile Touch: Clara Schumann\'s Repertoire Secrets',
                'category' => 'Classical Repertoire',
                'tags' => 'Piano, Cantabile, Schumann, Touch, Repertoire, Legato',
                'author_id' => $clara->id,
                'cover_image' => 'images/courses/piano.svg',
                'read_time_minutes' => 6,
                'excerpt' => 'How to extract a warm, singing bel canto tone from modern concert grand pianos without harsh percussive attack.',
                'body' => '<h3>Singing Through the Hammer and Felt</h3><p>The piano is nominally a percussion instrument, yet every classical virtuoso\'s lifelong quest is to persuade the listener that it possesses human lungs. In the romantic repertoire of Robert Schumann, Chopin, and Brahms, melodic lines must float with continuous vocal legato.</p><h4>Weight Transfer from the Shoulder</h4><p>To achieve a singing <em>cantabile</em> tone, play from the large muscles of the back and upper arm rather than pressing solely with the fingers. Think of transferring the natural weight of your forearm from finger cushion to finger cushion, as if walking on deep velvet.</p><ul><li><strong>Keep wrist supple:</strong> Allow gentle vertical cushioning at phrase climaxes.</li><li><strong>Pedal with the ear:</strong> Change damper pedal just after striking the bass root, catching the resonance cleanly without blurring harmonies.</li><li><strong>Voicing control:</strong> Ensure the melodic pinky is supported by a firm knuckle bridge, projecting above the accompaniment chords.</li></ul>',
            ],
            [
                'title' => 'Preparing for Conservatory Auditions: 5 Virtuoso Habits',
                'category' => 'Conservatory Life & News',
                'tags' => 'Auditions, Practice, Performance, Conservatory, Psychology',
                'author_id' => $franz->id,
                'cover_image' => 'images/courses/piano.svg',
                'read_time_minutes' => 5,
                'excerpt' => 'Practical mental and physical preparation protocols used by our audition juries when evaluating prospective students.',
                'body' => '<h3>Beyond Mere Notes: What the Conservatory Jury Seeks</h3><p>Every year, hundreds of aspiring recitalists perform technical etudes before our entrance audition committees. While accurate tempo and flawless fingerings are fundamental prerequisites, they alone will never secure admission. The jury evaluates <strong>artistic poise, architectural understanding, and expressive integrity</strong>.</p><ol><li><strong>Slow Tempo Mastery:</strong> If you cannot play a passage with complete musical intention at 50% tempo, you do not truly know it.</li><li><strong>Score Memorization without the Instrument:</strong> Hear every harmony and finger placement in your mind away from the piano or fingerboard.</li><li><strong>Simulate Performance Pressure:</strong> Record your run-throughs in a single continuous take every Friday afternoon.</li><li><strong>Understand Historical Context:</strong> Know when the work was written, the composer\'s life circumstances, and the harmonic language of the era.</li><li><strong>Physical Recovery:</strong> Gentle stretching, breath control, and adequate sleep are non-negotiable foundations for virtuosity.</li></ol>',
            ],
            [
                'title' => 'The Circle of Fifths & Harmonic Ear Training Explained',
                'category' => 'Music Theory & Ear Training',
                'tags' => 'Music Theory, Circle of Fifths, Ear Training, Harmony, Modulation',
                'author_id' => $clara->id,
                'cover_image' => 'images/courses/guitar.svg',
                'read_time_minutes' => 8,
                'excerpt' => 'Demystifying tonal relationships, secondary dominants, and cadence structures for developing musicians.',
                'body' => '<h3>The Compass of Western Harmony</h3><p>To the novice student, key signatures can seem like arbitrary collections of sharps and flats. In reality, the Circle of Fifths is nature\'s mathematical blueprint for tonal gravity and tension resolution.</p><p>By understanding how dominant chords naturally pull towards their tonic centers, improvisers and classical performers alike gain the ability to anticipate modulations, sight-read complex scores with ease, and craft satisfying voice leading in four-part harmony.</p>',
            ],
        ];

        foreach ($posts as $p) {
            $post = BlogPost::updateOrCreate(
                ['title' => $p['title']],
                [
                    'slug' => Str::slug($p['title']),
                    'category' => $p['category'],
                    'tags' => $p['tags'],
                    'author_id' => $p['author_id'],
                    'cover_image' => $p['cover_image'],
                    'read_time_minutes' => $p['read_time_minutes'],
                    'excerpt' => $p['excerpt'],
                    'body' => $p['body'],
                    'is_published' => true,
                    'published_at' => now()->subDays(rand(1, 30)),
                ]
            );

            // Seed sample approved and pending comments if student exists
            if ($student && $post->comments()->count() === 0) {
                BlogComment::create([
                    'blog_post_id' => $post->id,
                    'user_id' => $student->id,
                    'content' => 'This essay provided such tremendous clarity for my upcoming repertoire rehearsal. The advice on weight transfer completely changed how I voice the polyphonic chords!',
                    'status' => 'approved',
                    'created_at' => now()->subDays(2),
                ]);

                if ($post->id % 2 === 0) {
                    BlogComment::create([
                        'blog_post_id' => $post->id,
                        'user_id' => $student->id,
                        'content' => 'Could the faculty recommend the most authentic Henle or Bärenreiter edition for this specific movement?',
                        'status' => 'pending',
                        'created_at' => now()->subHours(3),
                    ]);
                }
            }
        }
    }
}
