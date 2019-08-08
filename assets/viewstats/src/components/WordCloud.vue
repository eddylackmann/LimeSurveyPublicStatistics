<template>
    <div class="col-xs-12">
        <div :id="wordcloudId" class="container-center" :style="{height: cloudHeight+'px', width: cloudWidth+'px'}" v-if="drawn">
            <vuewordcloud
                font-family="sans-serif"
                rotation-unit="deg"
                :animation-duration="350"
                :font-size-ratio="4"
                :words="curatedWordList"
                :color="simpleColor"
                :rotation="rotateWords"
            />
        </div>
        <div class="text-center" v-if="!drawn">
            <button class="btn btn-default" @click="drawWordCloud">Draw wordcloud</button>
        </div>
    </div>
    
</template>

<script>
                //:font-weight="weightByWeight"
import _ from 'lodash';
import {scaleLinear} from 'd3';
import { saveAs } from 'file-saver';
import {getSVGString, svgString2Image} from '../libs/processSVG.js';
import vuewordcloud from 'vuewordcloud';

export default {
    name: 'WordCloudComp',
    components: { vuewordcloud },
    props: {
        fieldId: {type: String, required: true},
        wordlist: {type: Array, required: true},
        wordCloudSettings: {type: Object, required: true}
    },
    data() {
        return {
            drawn: false
        }
    },
    computed: {
        curatedWordList() { return _.slice(this.wordlist, 0, this.wordCloudSettings.wordCount) },
        wordcloudId() { return 'WordCloud--imagecontainer-'+this.fieldId },
        cloudWidth() { 
            let baseValue = this.wordCloudSettings.cloudWidth;
            if((window.innerWidth-120) < baseValue) {
                baseValue = Math.round(window.innerWidth*0.9);
            }
            return baseValue;
        },
        cloudHeight() { 
            let baseValue = this.wordCloudSettings.cloudHeight;
             if((window.innerWidth-120) < this.cloudWidth) {
                baseValue = Math.round((this.cloudWidth/4)*3);
            }
            return baseValue;
        },
        fontPadding() { return this.wordCloudSettings.fontPadding },
        wordAngle() { return this.wordCloudSettings.wordAngle },
        minFontSize() { return this.wordCloudSettings.minFontSize },
        startColor() {
            return '#cd113b';
        },
        finalColor(){
            return '#213262';
        },
        colorArray() {
            const color = scaleLinear()
                .domain([0,4].reverse())
                .range([this.startColor,this.finalColor]);
            return color;
        },
        highestNumber() {
            return _.reduce(this.wordlist, (coll, wordArray) => {
                return coll < wordArray[1] ? coll : wordArray[1];
            }, 0);
        },
        lowestNumber() {
            return _.reduce(this.wordlist, (coll, wordArray) => {
                return coll > wordArray[1] ? coll : wordArray[1];
            }, 0);
        },
        medianNumber() {
            let diff = this.highestNumber - this.lowestNumber;
            return this.highestNumber-Math.round(diff/2);
        },
        firstQuartile() {
            let diff = this.highestNumber - this.lowestNumber;
            return this.highestNumber-Math.round(diff/4);
        },
        lastQuartile() {
            let diff = this.highestNumber - this.lowestNumber;
            return this.lowestNumber+Math.round(diff/4);
        },
    },
    methods: {
        drawWordCloud() {
            this.drawn = true;
        },
        rotateWords(val, weight) {
            if(weight%2 == 1) {
                return this.wordAngle;
            }
            return -this.wordAngle;

        },
        weightByWeight(val,weight) {
            if(weight <= this.highestNumber && weight > this.firstQuartile) {
                return 800;
            } else if(weight <= this.firstQuartile && weight > this.medianNumber) {
                return 700;
            } else if(weight <= this.medianNumber && weight > this.lastQuartile) {
                return 400;
            } else if(weight <= this.lastNumber && weight >= this.lowestNumber) {
                return 300;
            }
        },
        simpleColor(val,weight) {
            if(weight%2 == 1) {
                return this.startColor;
            }
            return this.finalColor;
        },
        colorByWeight(val,weight) {
            if(weight <= this.highestNumber && weight > this.firstQuartile) {
                return this.colorArray(0);
            } else if(weight <= this.firstQuartile && weight > this.medianNumber) {
                return this.colorArray(1);
            } else if(weight <= this.medianNumber && weight > this.lastQuartile) {
                return this.colorArray(2);
            } else if(weight <= this.lastNumber && weight >= this.lowestNumber) {
                return this.colorArray(3);
            }
        },
        triggerDownload() {
            const svgString = getSVGString($('#'+this.wordcloudId).find('svg').first()[0]);
            svgString2Image( svgString, 2*this.cloudWidth, 2*this.cloudHeight, 'png', (dataBlob, fileSize) => {
                saveAs( dataBlob, 'WordCloud-Question-'+this.fieldId );
            } ); // passes Blob and filesize String to the callback
        }
    }
}

</script>

<style>

</style>
