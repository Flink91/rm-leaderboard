// Define interfaces
export interface RecordDataRMC {
    submitTime: string;
    displayName: string;
    accountId: string;
    objective: 'wr' | 'author' | 'gold' | 'silver' | 'bronze';
    category: 'classic' | 'standard';
    goals: number;
    belowGoals: number;
    skips?: number;
    videoLink?: string;
}

export interface RecordDataRMS {
    submitTime: string;
    displayName: string;
    accountId: string;
    objective: 'wr' | 'author' | 'gold' | 'silver' | 'bronze';
    category: 'classic' | 'standard';
    goals: number;
    skips: number;
    timeSurvived: number;
    videoLink?: string;
}
