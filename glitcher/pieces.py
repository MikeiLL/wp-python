#!/usr/bin/env python
# encoding: utf=8
"""
one.py

Digest only the first beat of every bar.

By Ben Lacker, 2009-02-18.
"""
import echonest.remix.audio as audio
import sys

usage = """
Usage: 
    python i16o.py <input_filename> <output_filename>

Example:
    python pieces.py audio/CBShort.mp3 Abridged.mp3
    python pieces.py audio/malcolmmarsden.mp3 Abridged.mp3
    python pieces.py audio/GlitchBit_BJanoff.mp3 Abridged.mp3
    python pieces.py audio/FLAtRich-Level5.mp3 Abridged.mp3
"""

try:
    input_filename = sys.argv[1]
    output_filename = sys.argv[2]
except:
     print usage
     sys.exit(-1)
     

"""
    count number of beats before and after first and last bar, respectively
    optionally remove body of track for testing purposes,
    leaving pre-bar and post-bar beats plus first and last 4 bars
    if track has no bars, include all segments
"""

def abridge(bars, segments, count):
"""
remove track segments between COUNT bars at start and end
"""
    start = 0
    end = len(segments)
    for segment in segments:
        if segment.start < bars[count].start:
            start += 1
        elif segment.start > bars[-count].start:
            end -= 1
    del segments[start:end]
    return segments
    
def pre_post(beats, bars):
"""
return number of beats before first and after last bar
"""
    beats_in = 0
    beats_out = 0
    for beat in beats:
        if beat < bars[0].start:
            beats_in += 1
        if beat.start >= bars[-1].end:
            beats_out += 1
    return (beats_in, beats_out)

def main(input_filename, output_filename):
    audiofile = audio.LocalAudioFile(input_filename)
    duration = audiofile.analysis.duration
    segments = audiofile.analysis.segments
    bars = audiofile.analysis.bars
    beats = audiofile.analysis.beats
    print duration, " ", len(segments)
    beats_in = beats_out = 0
    
    if beats:
        in_flow = {"music": abridge(bars, segments, 4), "pre_bar_beats": pre_post(beats, bars)[0],
                   "post_bar_beats": pre_post(beats, bars)[1]}
    else:
        in_flow = {"music": segments, "pre_bar_beats": 0,
                   "post_bar_beats": 0}
        
    print in_flow['pre_bar_beats'], "pre-bar beats", in_flow['post_bar_beats'], "post-bar beats"
    
    out = audio.getpieces(audiofile, in_flow["music"])
    out.encode(output_filename)

if __name__ == "__main__":
    main(input_filename, output_filename)