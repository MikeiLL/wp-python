#!/usr/bin/env python
# encoding: utf=8

"""
capsule.py

accepts songs on the commandline, order them, beatmatch them, and output an audio file

Created by Tristan Jehan and Jason Sundram.
"""

print "Here you can see, if interested, some of what the EchoNest / Remix process was..."

import os
import sys
from optparse import OptionParser
from echonest.remix.action import render, make_stereo
from echonest.remix.audio import LocalAudioFile
from pyechonest import util
from append_support import order_tracks, equalize_tracks, resample_features, timbre_whiten, initialize, make_transition, terminate, FADE_OUT, display_actions, is_valid


def tuples(l, n=2):
    """ returns n-tuples from l.
        e.g. tuples(range(4), n=2) -> [(0, 1), (1, 2), (2, 3)]
    """
    return zip(*[l[i:] for i in range(n)])

def do_work(audio_files, options):

    inter = float(options.inter)
    trans = float(options.transition)
    order = bool(options.order)
    equal = bool(options.equalize)
    verbose = bool(options.verbose)
    
    # Get pyechonest/remix objects
    analyze = lambda x : LocalAudioFile(x, verbose=verbose, sampleRate = 44100, numChannels = 2)
    tracks = map(analyze, audio_files)
    for tr in tracks:
        if tr.analysis.bars:
            #print tr.analysis.bars[0], " - ", tr.analysis.bars[1], " - ", tr.analysis.beats[:3]
            #print len(tr.analysis.bars), "bars counted"
            #print len(tr.analysis.beats), "beats counted"
            #print len(tr.analysis.tatums), "tatums counted"
            bar1 = tr.analysis.bars[0]
            beat1 = tr.analysis.beats[0]
            tatum1 = tr.analysis.tatums[0]
            bar_end = tr.analysis.bars[-1]
            beat_end = tr.analysis.beats[-1]
            tatum_end = tr.analysis.tatums[-1]
            #print bar_end.end, beat_end.end, tatum_end.end, tr.analysis.segments[-1]
            #print "source filename:", tr.analysis.source.filename
            #print "source metadata:", dir(tr.analysis.metadata)
            #print "source get sample:", tr.analysis.source.getsample #dir()
            #print "fade out start:", tr.analysis.start_of_fade_out
            #print "tempo:", tr.analysis.tempo
            #print "time signature:", tr.analysis.time_signature
            #print "end of fade in:", tr.analysis.end_of_fade_in
            if tatum1.start != beat1.start:
               print  "****", tatum1, beat1, "****"
            if bar1.start != beat1.start:
                for position, beat in enumerate(tr.analysis.beats[:7]):
                    if beat.start == bar1.start:
                        print "#######################", beat, "#######################"
            print "bar 1 starts at", bar1.start, "and beat 1 starts at", beat1.start
            print "--------------------"
        else:
            #print "sections: ", tr.analysis.sections, " | "
            #print "segments: ", tr.analysis.segments[0], " | "
            #print "number of segments: ", len(tr.analysis.segments), " | "
            #print "bars: ", tr.analysis.bars, " | "
            #print "tatums: ", tr.analysis.tatums, " | "
            print "--------------------"
    # decide on an initial order for those tracks
    if order == True:
        if verbose: print "Ordering tracks..."
        tracks = order_tracks(tracks)
    
    if equal == True:
        equalize_tracks(tracks)
        if verbose:
            print
            for track in tracks:
                print "Vol = %.0f%%\t%s" % (track.gain*100.0, track.filename)
            print
    
    valid = []
    # compute resampled and normalized matrices
    for track in tracks: 
        if verbose: print "Resampling features for", track.filename
        track.resampled = resample_features(track, rate='beats')
        track.resampled['matrix'] = timbre_whiten(track.resampled['matrix'])
        # remove tracks that are too small
        if is_valid(track, inter, trans):
            valid.append(track)
        else:
            print "Invalid track"
        # for compatibility, we make mono tracks stereo
        track = make_stereo(track)
    tracks = valid
    
    if len(tracks) < 1: return []
    # Initial transition. Should contain 2 instructions: fadein, and playback.
    if verbose: print "Computing transitions..."
    start = initialize(tracks[0], inter, trans)
    
    # Middle transitions. Should each contain 2 instructions: crossmatch, playback.
    middle = []
    [middle.extend(make_transition(t1, t2, inter, trans)) for (t1, t2) in tuples(tracks)]
    
    # Last chunk. Should contain 1 instruction: fadeout.
    end = terminate(tracks[-1], FADE_OUT)
    
    return start + middle + end

def get_options(warn=False):
    usage = "usage: %s [options] <list of mp3s>" % sys.argv[0]
    parser = OptionParser(usage=usage)
    parser.add_option("-t", "--transition", default=8, help="transition (in seconds) default=8")
    parser.add_option("-i", "--inter", default=8, help="section that's not transitioning (in seconds) default=8")
    parser.add_option("-o", "--order", action="store_true", help="automatically order tracks")
    parser.add_option("-e", "--equalize", action="store_true", help="automatically adjust volumes")
    parser.add_option("-v", "--verbose", action="store_true", help="show results on screen")          
    parser.add_option("-u", "--the_user", default=8, help="name this mix")              
    parser.add_option("-p", "--pdb", default=True, help="dummy; here for not crashing when using nose")
    
    (options, args) = parser.parse_args()
    if warn and len(args) < 2: 
        parser.print_help()
    return (options, args)
    
def main():
    options, args = get_options(warn=True);
    for a in args:
        print "track = ", a;

    actions = do_work(args, options)
    verbose = bool(options.verbose)
    
    if verbose:
        display_actions(actions)
        print "Output Duration = %.3f sec" % sum(act.duration for act in actions)
    
        print "Rendering..."
    # Send to renderer
    theuser = str(options.the_user)
    final_file = theuser + ".mp3"
    print final_file
    render(actions, final_file, verbose)
    return 1
    
if __name__ == "__main__":
    main()
    # for profiling, do this:
    #import cProfile
    #cProfile.run('main()', 'capsule_prof')
    # then in ipython:
    #import pstats
    #p = pstats.Stats('capsule_prof')
    #p.sort_stats('cumulative').print_stats(30)
